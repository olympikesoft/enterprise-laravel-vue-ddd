<?php

namespace App\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Infrastructure\Persistence\Models\Campaign; // For status constants if needed

class UpdateCampaignHttpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled in the controller or handler
        // to check if the user owns the campaign or is an admin.
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 'sometimes' means the field is only validated if present in the request
        return [
            'title' => 'sometimes|required|string|max:255|min:5',
            'description' => 'sometimes|required|string|min:20',
            'goal_amount' => 'sometimes|required|numeric|min:1',
            'start_date' => 'sometimes|required|date', // Further date logic might be in handler
            'end_date' => 'sometimes|required|date|after:start_date',
            // Admin might be able to update status
            'status' => 'sometimes|required|string|in:' . implode(',', [
                Campaign::STATUS_PENDING,
                Campaign::STATUS_APPROVED,
                Campaign::STATUS_REJECTED,
                Campaign::STATUS_ACTIVE, // Assuming you might have this
                Campaign::STATUS_COMPLETED,
                Campaign::STATUS_CANCELLED,
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'The end date must be a date after the start date.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
}
