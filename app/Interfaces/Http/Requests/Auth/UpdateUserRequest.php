<?php

namespace App\Interfaces\Http\Requests\Auth;

use App\Application\DTO\User\UpdateUserDTO as UserUpdateUserDTO;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {

        /** @var User|null $actingUser */
        $actingUser = $this->user();
        return $actingUser && $actingUser->role === 'admin'; // Or $actingUser->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userToUpdateId = $this->route('user'); // This could be an int ID or a User model instance
        if ($userToUpdateId instanceof User) {
            $userToUpdateId = $userToUpdateId->id;
        }


        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userToUpdateId),
            ],
            'role' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['user', 'admin']), // Adjust roles as needed
            ],
            'isActive' => 'sometimes|required|boolean', // Ensure 'isActive' comes as 'true', 'false', 1, or 0
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'role.required' => 'The user role is required.',
            'role.in' => 'The selected role is invalid.',
            'isActive.required' => 'The active status is required.',
            'isActive.boolean' => 'The active status must be true or false.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This is a good place to convert 'true'/'false' strings to booleans if necessary,
     * though Laravel's 'boolean' rule handles common string representations.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('isActive')) {
            $this->merge([
            ]);
        }
    }


    /**
     * Creates a DTO from the validated request data.
     *
     * @return UserUpdateUserDTO
     */
    public function toDto(): UserUpdateUserDTO
    {
        $validated = $this->validated();

        $userToUpdateId = $this->route('user');
        if ($userToUpdateId instanceof User) {
            $userToUpdateId = $userToUpdateId->id;
        }

        return new UserUpdateUserDTO(
            userId: $userToUpdateId,
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            role: $validated['role'] ?? null,
            isActive: isset($validated['isActive']) ? (bool) $validated['isActive'] : null,
        );
    }
}
