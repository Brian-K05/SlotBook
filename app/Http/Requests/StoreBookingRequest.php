<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'slot_id.required' => 'Pick an hour first.',
            'slot_id.exists' => 'That hour is no longer on the book.',
            'name.required' => 'We need a name for the book.',
            'email.required' => 'We need an email to confirm the hour.',
            'email.email' => 'That email does not look right.',
        ];
    }
}
