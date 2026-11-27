<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\NewFollower;


class NotificationTest extends TestCase
{
    use RefreshDatabase;

    //verifica se o usuario consegue ver todas as suas notificacoes
    public function test_authenticated_user_can_view_their_notifications(): void
    {
        $user = User::factory()->create();

        //cria notificacoes para o usuario
        Notification::send(
            $user,
            new \App\Notifications\ChirpLiked(
                User::factory()->create(),
                \App\Models\Chirp::factory()->create()
            )
        );

        //o usuario logado faz requisicao para ver notificacoes
        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

            //verifica se pagina abriu e se conteudo apareceu
        $response->assertOk();
        $response->assertSee('Minhas notificações');
        $response->assertSee('curtiu sua publicação!');
    }


    //um usuário não pode visualizar as notificações de outra pessoa.
    public function test_user_can_only_see_their_own_notifications(): void
    {

        //cria dois usuários
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
    
        //cria notificacao para um dos usuarios
        Notification::send(
            $user,
            new \App\Notifications\ChirpLiked(
                User::factory()->create(),
                \App\Models\Chirp::factory()->create()
            )
        );
    
        //cria para o segundo
        Notification::send(
            $otherUser,
            new \App\Notifications\ChirpLiked(
                User::factory()->create(),
                \App\Models\Chirp::factory()->create()
            )
        );
    
        //o primeiro usuario logado tenta acessar as notificacoes
        $response = $this->actingAs($user)
            ->get(route('notifications.index'));
    
            //verifica resposta HTTP foi 200, e se existe o texto
        $response->assertOk();
        $response->assertSee('Minhas notificações');
    
        //verifica a quantidade de notificacoes totais
        $this->assertDatabaseCount('notifications', 2);
    
        //verifica a quantidade de notificacao do usuario que acessou
        $this->assertCount(
            1,
            $user->notifications
        );
    }

    //visitante nao pode ver as notificacoes
    public function test_guest_cannot_view_notifications(): void
{
    //tenta acessar sem login
    $response = $this->get(route('notifications.index'));

//verifica se houve redirecionamento para login
    $response->assertRedirect(route('login'));
}


//verifica o paginate
public function test_notifications_are_paginated(): void
{
    $user = User::factory()->create();

//cria 12 notificacoes para usuario
foreach (range(1, 12) as $number) {
    Notification::send(
        $user,
        new \App\Notifications\ChirpLiked(
            User::factory()->create(),
            \App\Models\Chirp::factory()->create()
        )
    );
}
    //usuario logado acessa suas notificacoes
    $response = $this->actingAs($user)
        ->get(route('notifications.index'));

        //verifica http 200
    $response->assertOk();

    //verifica se a primeira pagina tem 10 registros.
    $this->assertCount(10, $response->viewData('notifications'));

     //acessa segunda pagina
    $response = $this->actingAs($user)
        ->get(route('notifications.index', ['page' => 2]));

        //verifica http 200

    $response->assertOk();
  //verifica se a segunda tem 2 registros.
    $this->assertCount(2, $response->viewData('notifications'));
}



//verifica se as notificacoes de curtidas sao marcadas como lidas
public function test_user_can_mark_likes_notification_as_read(): void
{

    $user = User::factory()->create();
    $author = User::factory()->create();

    //cria chirp para usuario author
    $chirp = \App\Models\Chirp::factory()->create([
        'user_id' => $author->id,
    ]);

    //cria uma notificacao de um usuario para outro, informa quem curtiu e qual publicação recebeu a curtida.
    Notification::send(
        $user,
        new \App\Notifications\ChirpLiked($author, $chirp)
    );

    //Busca a primeira notificação recebida pelo usuário.
    $notification = $user->notifications()->first();

    //usuario logado acessa e marca a notificacao como lida
    $response = $this->actingAs($user)
        ->post(route('notifications.read', $notification->id));

        //Verifica se o usuário foi redirecionado para a publicação correta.
    $response->assertRedirect(route('chirps.show', $chirp->id));

    //busca no banco a notificacao e verifica se foi lida
    $this->assertNotNull(
        $notification->fresh()->read_at
    );
}



//usuário nao pode marcar a notificação de outra pessoa como lida
public function test_user_cannot_mark_another_users_notification_as_read(): void
{
    // cria usuarios
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $author = User::factory()->create();

    // cria um chirp para a notificação.
    $chirp = \App\Models\Chirp::factory()->create([
        'user_id' => $author->id,
    ]);

    // envia uma notificação para o outro usuário.
    Notification::send(
        $otherUser,
        new \App\Notifications\ChirpLiked($author, $chirp)
    );

    // recuperamos a notificação
    $notification = $otherUser->notifications()->first();

    // simula que o primeiro usuário tenta marcar essa notificação como lida.
    $response = $this->actingAs($user)
        ->post(route('notifications.read', $notification->id));

    // Esperamos uma resposta 403 (acesso proibido).
    $response->assertForbidden();

    // Garantimos que a notificação continua no banco e não foi marcada como lida.
    $this->assertNotNull($notification->fresh());
    $this->assertNull($notification->fresh()->read_at);
}

//usuario nao pode acessar uma notificacao inexistente
public function test_user_cannot_mark_nonexistent_notification_as_read(): void
{
    // Criamos um usuário autenticado.
    $user = User::factory()->create();

    // Tentamos acessar uma notificação com um ID inexistente.
    $response = $this->actingAs($user)
        ->post(route('notifications.read', 'id-inexistente'));

    // Verificamos se o Laravel retornou erro 404.
    $response->assertNotFound();
}



// verifica se a notificação de novo seguidor é marcada como lida
public function test_user_can_mark_follower_notification_as_read(): void
{
    //cria dois usuarios
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    // cria a notificação de novo seguidor.
    $followed->notify(new NewFollower($follower));

    // recupera a notificação.
    $notification = $followed->notifications()->first();

    // o usuário que recebeu a notificação tenta marcá-la como lida.
    $response = $this->actingAs($followed)
        ->post(route('notifications.read', $notification->id));

    //verifica se foi redirecionado para o perfil do novo seguidor.
    $response->assertRedirect(route('profile.show', $follower->id));

    //verifica se a notificação foi marcada como lida.
    $this->assertNotNull($notification->fresh()->read_at);
}

}