<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Valida los campos de login.
     * 👉 El captcha se exige solo a partir del 3er intento fallido.
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string|email',
            'password'        => 'required|string',
        ];

        if ($this->limiteDeIntentosAlcanzado($request)) {
            $rules['captcha'] = 'required|captcha';
        }

        $messages = [
            'captcha.required' => __('auth.captcha'),
            'captcha.captcha'  => __('auth.captcha'),
        ];

        $request->validate($rules, $messages);
    }

    /**
     * Determina si ya se alcanzaron 2 intentos fallidos previos.
     */
    protected function limiteDeIntentosAlcanzado(Request $request)
    {
        return $this->limiter()->attempts($this->throttleKey($request)) >= 2;
    }

    /**
     * Credenciales personalizadas: requiere que estado sea true.
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['estado' => true]
        );
    }

    /**
     * Respuesta cuando el login falla.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where($this->username(), $request->{$this->username()})->first();

        if ($user && ! $user->estado) {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.inactive')],
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
