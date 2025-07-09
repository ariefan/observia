<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLivestockRequest extends FormRequest
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
            'tag_id' => ['required', 'string', 'max:255'],
            'birth_weight' => ['nullable', 'numeric'],
            'weight' => ['nullable', 'numeric'],
            'breed_id' => ['required', 'uuid', 'exists:breeds,id'],
            'male_parent_id' => ['nullable', 'uuid', 'exists:livestocks,id'],
            'female_parent_id' => ['nullable', 'uuid', 'exists:livestocks,id'],
            'photo' => ['nullable', 'array'],
            'photo.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Allow strings (existing photo paths) or image files
                    if (!is_string($value) && !$value instanceof \Illuminate\Http\UploadedFile) {
                        $fail('The '.$attribute.' must be a string or an image file.');
                        return;
                    }
                    
                    // If it's an uploaded file, validate as image
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        if (!$value->isValid()) {
                            $fail('The '.$attribute.' must be a valid file.');
                            return;
                        }
                        
                        if (!in_array($value->getClientOriginalExtension(), ['jpeg', 'jpg', 'png', 'gif', 'svg'])) {
                            $fail('The '.$attribute.' must be a file of type: jpeg, jpg, png, gif, svg.');
                            return;
                        }
                        
                        if ($value->getSize() > 2048 * 1024) { // 2MB
                            $fail('The '.$attribute.' may not be greater than 2MB.');
                            return;
                        }
                    }
                }
            ],
            'barter_livestock_id' => ['nullable', 'string', 'max:255'],
            'barter_from' => ['nullable', 'string', 'max:255'],
            'barter_date' => ['nullable', 'date'],
            'purchase_from' => ['nullable', 'string', 'max:255'],
            'grant_from' => ['nullable', 'string', 'max:255'],
            'grant_date' => ['nullable', 'date'],
            'borrowed_from' => ['nullable', 'string', 'max:255'],
            'borrowed_date' => ['nullable', 'date'],
            'entry_date' => ['nullable', 'date'],
        ];
    }
}
