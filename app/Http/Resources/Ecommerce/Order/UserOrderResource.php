<?php

namespace App\Http\Resources\Ecommerce\Order;

use App\Http\Resources\Ecommerce\Shipping\ShippingVariantResource;
use App\Http\Resources\User\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderResource extends JsonResource
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

        $data['status'] = $this->status ? $this->status->label() : null;

        if (in_array('shipping', $includes)) {
            $data['shipping'] = new ShippingVariantResource($this->shipping);
            unset($data['shipping_variant_id']);
        }

        if (in_array('payments', $includes)) {
            $data['payments'] = PaymentResource::collection($this->payments);
        }

        if (in_array('address', $includes)) {
            $data['user_address']['address'] = "{$this->address->country->name} - {$this->address->state->name} - {$this->address->city->name} - {$this->address->address}";
            $data['user_address']['postal_code'] = $this->address->postal_code;
            $data['user_address']['phone_number'] = $this->address->phone_number;
            unset($data['user_address_id']);
        }

        if (in_array('items', $includes)) {
            $data['items'] = OrderItemResource::collection($this->items);
        } else {
            unset($data['items']);
        }

        return $data;
    }
}
