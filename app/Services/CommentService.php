<?php

namespace App\Services;

use App\Models\User;
use App\Models\Chirp;
use App\Models\Comment;
use App\Notifications\CommentAdded;

class CommentService
{
    public function createComment(
        User $user,
        Chirp $chirp,
        string $message
    ): Comment {

        // Cria o comentário associado à publicação.
        $comment = $chirp->comments()->make([
            'message' => $message,
        ]);

        // Associa o comentário ao usuário.
        $comment->user()->associate($user);

        // Salva o comentário no banco de dados.
        $comment->save();

        // Verifica se o comentário foi feito na publicação de outra pessoa.
        if ($chirp->user_id !== $user->id) {

            // Notifica o dono da publicação.
            $chirp->user->notify(
                new CommentAdded(
                    $user,
                    $chirp,
                    $comment
                )
            );
        }

        return $comment;
    }
}