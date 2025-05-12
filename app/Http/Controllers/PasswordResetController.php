<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Muestra el formulario de restablecimiento de contraseña.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Inertia\Response
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');

        // Validar que el email y el token sean válidos
        if (!$email || !is_string($token)) {
            return inertia('Auth/ResetPassword', [
                'error' => __('The password reset link is invalid.'),
                'token' => null,
                'email' => null,
            ]);
        }

        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Procesa el restablecimiento de contraseña.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        // Intentar restablecer la contraseña usando Jetstream
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Manejar el resultado
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('Password updated successfully. Please log in.'));
        }

        return redirect()->back()->withErrors(['email' => __($status)]);
    }
}
