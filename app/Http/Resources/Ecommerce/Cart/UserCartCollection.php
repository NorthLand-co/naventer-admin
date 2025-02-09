<?php

namespace App\Http\Resources\Ecommerce\Cart;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCartCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
