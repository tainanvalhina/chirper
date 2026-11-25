<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;
use App\Notifications\ChirpLiked;


class LikeController extends Controller
{
    public function store(Chirp $chirp): RedirectResponse
    {
        $like = $chirp->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);
    
        if ($like->wasRecentlyCreated && $chirp->user_id !== auth()->id()) {
            $chirp->user->notify(
                new ChirpLiked(auth()->user(), $chirp)
            );
        }
    
        return back();
    }

    //busca a curtida do usuario e exclui
    public function destroy(Chirp $chirp): RedirectResponse
    {
        $chirp->likes()
            ->where('user_id', auth()->id())
            ->delete();

        return back();
    }
}