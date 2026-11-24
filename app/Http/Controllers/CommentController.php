<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    //recebe os dados do formulario e identifica automaticamente qual chirp recebeu comentario
    public function store(Request $request, Chirp $chirp): RedirectResponse
    {
        //exige uma mensagem, que deve ser texto e ter no máximo 1.000 caracteres.
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        //cria o comentário associado à publicação.
        $comment = $chirp->comments()->make([
            'message' => $validated['message'],
        ]);
        
        $comment->user()->associate($request->user());

        $comment->save();

        return back();
    }

    public function destroy(Comment $comment): RedirectResponse
{
    $user = auth()->user();

    // Verifica se o usuário é o autor do comentário
    // ou o autor da publicação.
    if (
        $comment->user_id !== $user->id &&
        $comment->chirp->user_id !== $user->id
    ) {
        abort(403);
    }

    $comment->delete();

    return back();
}
}