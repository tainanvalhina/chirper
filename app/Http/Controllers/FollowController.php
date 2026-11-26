<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Notifications\NewFollower;


class FollowController extends Controller
{
    
public function store(Request $request, User $user): RedirectResponse
{
    // Usuário que está começando a seguir alguém.
    $follower = $request->user();

    // Impede que o usuário siga a si mesmo.
    if ($follower->id === $user->id) {
        abort(403);
    }

    // Cria o relacionamento ou recupera um existente.
    $follow = $follower->following()->firstOrCreate([
        'following_id' => $user->id,
    ]);

    // Só envia notificação se o relacionamento acabou de ser criado.
    if ($follow->wasRecentlyCreated) {
        $user->notify(new NewFollower($follower));
    }

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