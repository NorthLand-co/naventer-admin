<?php

namespace App\Http\Requests\Ecommerce\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserOrderRequest extends FormRequest
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
            'description' => 'nullable|string',
            'address' => 'required|exists:user_addresses,id',
            'type' => 'required|string|regex:/^cart_shipping_/|max:255',
        ];
    }
}
