<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\CommentAdded;

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

//autor do comentario pode excluir seu proprio comentario
public function test_comment_author_can_delete_their_comment(): void
{
    //Cria um usuário e um Chirp.
    $user = User::factory()->create();
    $chirp = Chirp::factory()->create();

    //cria um comentario
    $comment = $chirp->comments()->make([
        'message' => 'Comentário para excluir',
    ]);

    //associa ao user e salva
    $comment->user()->associate($user);
    $comment->save();

//usuario autenticado executa a acao de exclusao
    $response = $this->actingAs($user)
        ->delete(route('comments.destroy', $comment));

        //verifica se apos excluir, houve redirecionamento
    $response->assertRedirect();

    //verifica se determinado registro não existe mais no banco de dados.
    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
}

//verifica se autor do chirp pode excluir comentario de outra pessoa
public function test_chirp_author_can_delete_comment(): void
{
    //cria author do chirp e usuario do comentario
    $chirpOwner = User::factory()->create();
    $commentAuthor = User::factory()->create();

    //cria um chirp associando ao autor
    $chirp = Chirp::factory()->create([
        'user_id' => $chirpOwner->id,
    ]);

    //cria um comentario e associa a outro usuario
    $comment = $chirp->comments()->make([
        'message' => 'Comentário de outra pessoa',
    ]);
    $comment->user()->associate($commentAuthor);
    $comment->save();


    //autor do chirp requisita acao de exclusao
    $response = $this->actingAs($chirpOwner)
        ->delete(route('comments.destroy', $comment));

        //verifica redirecionamento
    $response->assertRedirect();

     //verifica se determinado registro não existe mais no banco de dados.
    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
}

//outro usuario nao pode excluir comentario
public function test_other_user_cannot_delete_comment(): void
{
    //cria usuarios
    $chirpOwner = User::factory()->create();
    $commentAuthor = User::factory()->create();
    $otherUser = User::factory()->create();


    //autor do chirp cria chirp
    $chirp = Chirp::factory()->create([
        'user_id' => $chirpOwner->id,
    ]);

//gera comentario e associa a um usuario
    $comment = $chirp->comments()->make([
        'message' => 'Comentário protegido',
    ]);
    $comment->user()->associate($commentAuthor);
    $comment->save();

    //um terceiro usuario tenta excluir
    $response = $this->actingAs($otherUser)
        ->delete(route('comments.destroy', $comment));

        //verifica se a aplicação respondeu com HTTP 403 (Forbidden).
    $response->assertForbidden();

    //verifica se o comentário continua existindo no banco de dados.
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
    ]);
}

// verifica se comentar na publicação de outra pessoa gera notificação
public function test_commenting_on_another_users_chirp_creates_notification(): void
{
    // Cria o dono da publicação e quem fará o comentário.
    $chirpOwner = User::factory()->create();
    $commentAuthor = User::factory()->create();

    // Cria uma publicação pertencente ao primeiro usuário.
    $chirp = Chirp::factory()->create([
        'user_id' => $chirpOwner->id,
    ]);

    // O segundo usuário comenta na publicação.
    $this->actingAs($commentAuthor)
        ->post(route('comments.store', $chirp), [
            'message' => 'Gostei da sua publicação!',
        ]);

    // Busca a notificação recebida pelo dono da publicação.
    $notification = $chirpOwner->notifications()
        ->where('type', CommentAdded::class)
        ->first();

    // Verifica se a notificação foi criada.
    $this->assertNotNull($notification);

    // Verifica se os dados da notificação estão corretos.
    $this->assertEquals(
        $commentAuthor->id,
        $notification->data['user_id']
    );

    $this->assertEquals(
        $chirp->id,
        $notification->data['chirp_id']
    );
}


// verifica se comentar na própria publicação não gera notificação
public function test_commenting_on_own_chirp_does_not_create_notification(): void
{
    // cria usuáriod
    $user = User::factory()->create();
    $chirp = Chirp::factory()->create([
        'user_id' => $user->id,
    ]);

    // o próprio dono comenta na publicação.
    $this->actingAs($user)
        ->post(route('comments.store', $chirp), [
            'message' => 'Meu próprio comentário!',
        ]);

    // Verifica que nenhuma notificação foi criada.
    $this->assertDatabaseCount('notifications', 0);
}
}