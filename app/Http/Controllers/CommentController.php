<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\CommentService;


class CommentController extends Controller
{

    public function __construct(
        private CommentService $commentService
    ) {
    }


    public function store(Request $request, Chirp $chirp): RedirectResponse
{
    // Valida a mensagem do comentário.
    $validated = $request->validate([
        'message' => ['required', 'string', 'max:1000'],
    ]);

    // Executa a criação do comentário através do Service.
    $this->commentService->createComment(
        $request->user(),
        $chirp,
        $validated['message']
    );

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