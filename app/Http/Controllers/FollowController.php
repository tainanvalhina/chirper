<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $follower = $request->user();

        //impedindo que alguém siga a si mesmo
        if ($follower->id === $user->id) {
            abort(403);
        }

        //procura um relacionamento ja existente, se nao existir, cria um novo
        $follower->following()->firstOrCreate([
            'following_id' => $user->id,
        ]);

        //retorna a pagina anterior
        return back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
{
    $follower = $request->user();

//procurando, entre as pessoas que o usuário logado segue, aquela cujo ID corresponde ao perfil visitado.
    $follower->following()
        ->where('following_id', $user->id)
        ->delete();

    return back();
}

public function followers(User $user): View
{
    //busca os relacionamentos em que pessoas seguem o usuario.
    $follows = $user->followers()->with('follower')->get();

    return view('profile.followers', compact('user', 'follows'));
}

public function following(User $user): View
{
   //busca pessoas que sao seguidas pelo usuario.
       $follows = $user->following()->with('following')->get();

    return view('profile.following', compact('user', 'follows'));
}

}