<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Logout extends Controller
{
    public function __invoke(Request $request)
    {

        //encerra a autenticação do usuário.
        Auth::logout();

        // Invalida a sessão atual.
        $request->session()->invalidate();
        //gera um novo token CSRF para as próximas requisições.
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }
}
