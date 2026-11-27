<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Notifications\CommentAdded;

class CommentController extends Controller
{
    //recebe os dados do formulario e identifica automaticamente qual chirp recebeu comentario
    public function store(Request $request, Chirp $chirp): RedirectResponse
    {
        // Valida a mensagem do comentário.
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);
    
        // Cria o comentário associado à publicação.
        $comment = $chirp->comments()->make([
            'message' => $validated['message'],
        ]);
    
        // Associa o comentário ao usuário autenticado.
        $comment->user()->associate($request->user());
    
        // Salva o comentário no banco.
        $comment->save();
    
        // Verifica se quem comentou não é o dono da publicação.
        if ($chirp->user_id !== $request->user()->id) {
    
            // Notifica o dono da publicação.
            $chirp->user->notify(
                new CommentAdded(
                    $request->user(),
                    $chirp,
                    $comment
                )
            );
        }
    
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