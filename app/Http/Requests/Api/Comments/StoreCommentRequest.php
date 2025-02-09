<?php

namespace App\Http\Requests\Api\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|string',
            'score' => 'nullable|numeric|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'guest_name' => 'nullable|required_if:user_id,null|string|max:255',
            'guest_email' => 'nullable|required_if:user_id,null|max:255',
        ];
    }
}
