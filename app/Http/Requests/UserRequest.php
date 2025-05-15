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
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255'],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:10',
            'pais_id' => 'nullable|exists:paises,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];

        if ($this->isMethod('post')) {
            // Creación: email debe ser único sin ignorar ningún ID
            $rules['email'][] = 'unique:users,email';
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Edición: ignorar el ID del usuario actual
            $rules['email'][] = 'unique:users,email,' . $this->route('user')->id;
        }

        return $rules;
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
