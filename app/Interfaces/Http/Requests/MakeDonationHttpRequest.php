<?php

namespace App\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakeDonationHttpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'amount' => 'required|numeric|min:0.50', // Minimum donation amount
            'message' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|in:USD,EUR,GBP', // Add other currencies as needed
            'payment_token' => 'nullable|string', // Token from payment gateway (e.g., Stripe.js)
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.exists' => 'The selected campaign does not exist.',
        ];
    }
}
