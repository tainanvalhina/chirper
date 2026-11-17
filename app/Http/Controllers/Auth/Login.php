<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    public function __invoke(Request $request)
    {
        // verifica se o e-mail e a senha foram enviados corretamente.
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //procura um usuário pelo e-mail e compara a senha informada com o hash armazenado no banco.
        //Quando o Remember Me está ativado, o Laravel utiliza um cookie persistente e o remember_token da tabela users para permitir que o usuário seja reconhecido em visitas futuras.
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            //Se o login funcionar, session()->regenerate() cria um novo identificador de sessão por segurança.
            $request->session()->regenerate();

            // leva o usuário à página que ele tentou acessar anteriormente. Se não houver uma página anterior registrada, vai para /
            return redirect()->intended('/')->with('success', 'Welcome back!');
        }

        // If login fails, redirect back with error
        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }
}
