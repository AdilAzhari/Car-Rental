<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileImageUploadRequest extends FormRequest
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
        $maxSize = config('image.profile_images.max_file_size');
        $allowedTypes = implode(',', config('image.allowed_types'));

        return [
            'image' => [
                'required',
                'file',
                'image',
                "max:{$maxSize}",
                "mimes:{$allowedTypes}",
                'dimensions:max_width=2000,max_height=2000',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    #[\Override]
    public function messages(): array
    {
        $maxSize = config('image.profile_images.max_file_size');
        $allowedTypes = implode(', ', config('image.allowed_types'));

        return [
            'image.required' => 'Please select an image to upload.',
            'image.file' => 'The uploaded file must be a valid file.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => "The image size must not exceed {$maxSize}KB.",
            'image.mimes' => "The image must be of type: {$allowedTypes}.",
            'image.dimensions' => 'The image dimensions are too large. Maximum allowed is 2000x2000 pixels.',
        ];
    }
}
