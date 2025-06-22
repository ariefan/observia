<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'picture' => 'nullable|string',
            'picture_blob' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'latlong' => 'required|array',
            'latlong.latitude' => 'required|numeric',
            'latlong.longitude' => 'required|numeric',
        ];
    }
}
