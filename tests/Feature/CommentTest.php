<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    //usuario autenticado pode criar um comentario
    public function test_authenticated_user_can_comment_on_a_chirp(): void
    {
        // cria um usuario e um chirp
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create();

        // usuario executa a acao no chirp
        $response = $this->actingAs($user)
            ->post(route('comments.store', $chirp), [
                'message' => 'Meu primeiro comentário!',
            ]);

        // verifica se houve redirecionamento.
        $response->assertRedirect();

        //verifica se o comentário realmente foi salvo no banco.
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'chirp_id' => $chirp->id,
            'message' => 'Meu primeiro comentário!',
        ]);
    }

    //visitante nao pode comentar chirp
    public function test_guest_cannot_comment_on_a_chirp(): void
{
    // cria um chirp
    $chirp = Chirp::factory()->create();

    // visitante faz a acao
    $response = $this->post(route('comments.store', $chirp), [
        'message' => 'Tentativa de comentário!',
    ]);

    // verifica se o Laravel redirecionou o visitante para o login.
    $response->assertRedirect(route('login'));

    //garante que o comentário não foi salvo.
    $this->assertDatabaseMissing('comments', [
        'chirp_id' => $chirp->id,
        'message' => 'Tentativa de comentário!',
    ]);
}

//sistema nao permite comentario vazio
public function test_comment_message_is_required(): void
{
    // cria um usuario e um chirp
    $user = User::factory()->create();
    $chirp = Chirp::factory()->create();


    $commentCount = $chirp->comments()
    ->where('user_id', $user->id)
    ->count();

    // usuario tenta criar um comentario vazio
    $response = $this->actingAs($user)
        ->post(route('comments.store', $chirp), [
            'message' => '',
        ]);

    // verifica erro
    $response->assertSessionHasErrors('message');

    //garante que o numero de comentario nao foi alterado
    $this->assertSame(
        $commentCount,
        $chirp->comments()->where('user_id', $user->id)->count()
    );
}

//comentario com mais de mil caracteres nao é salvo
public function test_comment_message_cannot_exceed_1000_characters(): void
{
    // Arrange
    $user = User::factory()->create();
    $chirp = Chirp::factory()->create();

    //conta o numero de comentarios existentes
    $commentCount = $chirp->comments()
    ->where('user_id', $user->id)
    ->count();

    // acao com o a repetindo 1001x
    $response = $this->actingAs($user)
        ->post(route('comments.store', $chirp), [
            'message' => str_repeat('a', 1001),
        ]);

    // verifica erro
    $response->assertSessionHasErrors('message');

    //verifica se aumentou a quantidade no banco
    $this->assertSame(
        $commentCount,
        $chirp->comments()->where('user_id', $user->id)->count()
    );
}
}