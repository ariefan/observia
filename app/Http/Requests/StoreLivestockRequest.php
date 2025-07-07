<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivestockRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'sex' => ['required', 'in:M,F'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric'],
            'origin' => ['required', 'numeric'],
            'status' => ['required', 'numeric'],
            'tag_type' => ['nullable', 'string', 'max:255'],
            'tag_id' => ['nullable', 'string', 'max:255'],
            'birth_weight' => ['nullable', 'numeric'],
            'weight' => ['nullable', 'numeric'],
            'breed_id' => ['required', 'uuid', 'exists:breeds,id'],
            'male_parent_id' => ['nullable', 'uuid', 'exists:livestocks,id'],
            'female_parent_id' => ['nullable', 'uuid', 'exists:livestocks,id'],
            'photo' => ['nullable', 'array'],
            'photo.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'barter_livestock_id' => ['nullable', 'string', 'max:255'],
            'barter_from' => ['nullable', 'string', 'max:255'],
            'barter_date' => ['nullable', 'date'],
        ];
    }
}
