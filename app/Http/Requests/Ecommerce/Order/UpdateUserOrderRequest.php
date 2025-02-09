<?php

namespace App\Http\Requests\Ecommerce\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'address' => 'exists:user_addresses,id',
            'type' => 'string|regex:/^cart_shipping_/|max:255',
        ];
    }
}
