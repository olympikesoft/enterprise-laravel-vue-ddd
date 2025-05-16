<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Infrastructure\Persistence\Models\Campaign; // For status constants if needed

class StoreCampaignHttpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone authenticated can attempt to create a campaign
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
            'title' => 'required|string|max:255|min:5',
            'description' => 'required|string|min:20',
            'goal_amount' => 'required|numeric|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'The end date must be a date after the start date.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
        ];
    }
}