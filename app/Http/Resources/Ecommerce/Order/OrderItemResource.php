<?php

namespace App\Http\Resources\Ecommerce\Order;

use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $includes = explode(',', $request->query('include', ''));
        $data = parent::toArray($request);

        if ($data['item_info']) {
            $data['item_info'] = json_decode($data['item_info']);
            $data['item_info']->name = Product::find($data['item_info']->price->product_id)->name;
        }

        if (in_array('product', $includes)) {
            $data['product'] = new ProductTransformer($this->product);
        }

        return $data;
    }
}
