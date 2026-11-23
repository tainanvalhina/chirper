<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function store(Chirp $chirp): RedirectResponse
    {
        //pega usuario autenticado, procura uma curtida daquele usuario, se ja existir mantem, se nao existir cria nova
        $chirp->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

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