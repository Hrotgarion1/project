<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user?->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:10',
            'pais_id' => 'nullable|exists:paises,id',
            'password' => 'nullable|string|min:8|confirmed', // Opcional en creación y actualización
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio'),
            'email.required' => __('El email es obligatorio'),
            'email.unique' => __('El email ya se encuentra registrado'),
            'password.min' => __('La contraseña debe tener al menos 8 caracteres'),
            'password.confirmed' => __('La confirmación de la contraseña no coincide'),
        ];
    }
}
