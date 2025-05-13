<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        //Nuevo, para que cada nuevo usuario que se registre por defecto tenga el rol de invitado
    $userInput = [
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
    ];

    if (isset($input['address'])) {
        $userInput['address'] = $input['address'];
    }

    if (isset($input['zip'])) {
        $userInput['zip'] = $input['zip'];
    }

    if (isset($input['phone'])) {
        $userInput['phone'] = $input['phone'];
    }

    $created_user = User::create($userInput);

    // Verifica si el formulario proporciona roles
    if (isset($input['role'])) {
        // Asigna roles específicos del formulario
        $created_user->assignRole($input['role']);
    } else {
        // Si no hay roles específicos, asigna el rol de invitado por defecto
        $created_user->assignRole('invitado');
    }

    return $created_user;
   }
}
