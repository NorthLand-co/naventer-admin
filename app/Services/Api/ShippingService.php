<?php

namespace App\Services\Api;

use App\Models\ProductVariant;
use App\Models\ShippingVariant;
use App\Models\UserCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ShippingService
{
    protected $userCartService;

    public function __construct(UserCartService $userCartService)
    {
        $this->userCartService = $userCartService;
    }

    public function getShippingInfo($productVariantId)
    {
        $productVariant = ProductVariant::with(['product.shipping.rates', 'product.shipping.method'])->find($productVariantId);
        if ($productVariant && $productVariant->product) {
            $shipping = $productVariant->product->shipping;
            if ($shipping && $shipping->isNotEmpty()) {
                return $shipping; // Return the shipping information if it's not empty
            }
        }

        return null;
    }

    private function getShipmentMapProduct()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $sessionId = Session::get('temporary_token') ?? null;

        $products = UserCart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
        })
            ->with(['product', 'productPrice', 'productVariant', 'product.shipping', 'product.shipping.rates', 'product.shipping.method'])
            ->get();

        $grouped = $products->groupBy(function ($item) {
            if ($item['product']['shipping']->isEmpty()) {
                $defaultShippingVariants = ShippingVariant::with(['rates', 'method'])->where('is_default', true)->get()->toArray();
                $item['product']['shipping'] = $defaultShippingVariants;
            }

            return collect($item['product']['shipping'])->first()['id']; // Group by shipping ID
        })->map(function ($group) {
            $shipping = collect($group->first()['product']['shipping'])->first(); // Shipping info is the same within the group
            $shipping['products'] = $group->map(function ($item) {
                return [
                    'cart' => collect($item)->except(['shipping', 'product', 'product_variant'])->toArray(),
                    'product' => collect($item['product'])->except('shipping')->toArray(),
                    'productVariant' => $item['productVariant'],
                    'productPrice' => $item['productPrice'],
                ];
            })->values()->toArray();

            return $shipping;
        })->values()->toArray();

        return $grouped;
    }

    public function calculateProductShipping(array $rates, $product, $price = 0, $weight = 0)
    {
        $additional_price = 0;

        foreach ($rates as $rate) {
            $conditions = json_decode($rate['conditions'], true);

            $match = true; // Assume conditions match initially

            // Check each condition dynamically
            foreach ($conditions as $attribute => $rules) {
                foreach ($rules as $rule) {
                    $operand = $rule['operand'];
                    $value = $rule['value'];
                    // Match condition based on the attribute type
                    switch ($attribute) {
                        case 'category':
                            if ($product && $product['category_id'] && ! compareWithOperand($product['category_id'], $operand, $value)) {
                                $match = false;
                            }
                            break;
                        case 'product':
                            if ($product && $product['id'] && ! compareWithOperand($product['id'], $operand, $value)) {
                                $match = false;
                            }
                            break;
                        case 'weight':
                            if (! compareWithOperand($weight, $operand, $value)) {
                                $match = false;
                            }
                            break;
                        case 'price':
                            if (! compareWithOperand($price, $operand, $value)) {
                                $match = false;
                            }
                            break;

                            // Add more cases here for other attributes like size, etc.

                            // default:
                            //     break;
                    }

                    // If any condition fails, break out of the loop
                    if (! $match) {
                        break;
                    }
                }

                if (! $match) {
                    break; // If one condition fails, skip to the next shipping rate
                }
            }

            // If all conditions match, add the additional price
            if ($match) {
                $additional_price += $rate['additional_price'];
            }
        }

        return $additional_price;
    }

    public function calculateProductListShipping(array $products, array $rates, int $basePrice)
    {
        $totalShipping = [
            'weight' => 0,
            'price' => 0,
            'send_price' => $basePrice,
        ];

        foreach ($products as $product) {
            $totalShipping['send_price'] += $this->calculateProductShipping($rates, $product['product']);
            $totalShipping['weight'] += $product['productVariant']->weight ?? 0 * $product['cart']['quantity'] ?? 1;
            $totalShipping['price'] += ($product['productPrice']->price ?? 0) * ($product['cart']['quantity'] ?? 1);
        }
        $totalShipping['send_price'] += $this->calculateProductShipping($rates, null, $totalShipping['price'], $totalShipping['weight']);

        return $totalShipping['send_price'] > 0 ? $totalShipping['send_price'] : 0;
    }

    public function calculateCartShipping()
    {
        $cartItems = $this->userCartService->getUserCartList();
        $cartItemsCount = count($cartItems);
        $shipmentInfoList = $this->getShipmentMapProduct();
        $shipmentResult = [];

        foreach ($shipmentInfoList as $key => $shipmentInfo) {
            if (count($shipmentInfo['products']) === $cartItemsCount) {
                $shipping_price = $this->calculateProductListShipping($shipmentInfo['products'], $shipmentInfo['rates'], $shipmentInfo['base_price']);
                $uniqueKey = 'cart_shipping_'.(string) Str::uuid();
                $shipmentResult = [];
                $shipmentResult[] = [
                    'key' => $uniqueKey,
                    'price' => $shipping_price,
                    'method' => $shipmentInfo['method'],
                    'variant' => [
                        'id' => $shipmentInfo['id'],
                        'name' => $shipmentInfo['name'],
                    ],
                ];
                Cache::put($uniqueKey, $shipmentResult[0], 86400);
                break;
            }
        }

        return $shipmentResult;
    }
}
