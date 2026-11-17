<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Controller
{
    public function __invoke(Request $request)
    {
        // validacao dos dados
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        //cria o usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            //transforma a senha em um hash seguro antes de armazená-la
            'password' => Hash::make($validated['password']),
        ]);

        // login automatico apos criar
        Auth::login($user);

        // direciona para home
        return redirect('/')->with('success', 'Welcome to Chirper!');
    }
}
