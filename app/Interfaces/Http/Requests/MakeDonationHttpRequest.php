<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakeDonationHttpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
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
            'donor_name' => 'required_without:user_id|nullable|string|max:255', // Required if user is not authenticated/provided
            'message' => 'nullable|string|max:1000',
            'payment_token' => 'required|string', // Token from payment gateway (e.g., Stripe.js)
        ];
    }

    public function messages(): array
    {
        return [
            'donor_name.required_without' => 'Your name is required for anonymous donations.',
            'campaign_id.exists' => 'The selected campaign does not exist.',
        ];
    }
}