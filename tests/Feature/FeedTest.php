<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chirp;

class FeedTest extends TestCase
{

    use RefreshDatabase;

    //usuario consegue ver seus proprios chirps no feed
    public function test_user_can_see_their_own_chirps_in_feed(): void
    {
        // Arrange cria user
        $user = User::factory()->create();
    
        //cria chirp do usuario
        $chirp = Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Meu primeiro Chirp',
        ]);
    
        // Act simulamos o acesso à página inicial como usuário autenticado.
        $response = $this->actingAs($user)->get('/');
    
        // Assert verifica se pagina carregou e se o chirp do usuario aparece nela
        $response->assertOk();
        $response->assertSeeText('Meu primeiro Chirp');
    }


    //verifica se usuario consegue visualizar chirps de seus seguidores
    public function test_user_can_see_chirps_from_users_they_follow(): void
{
    // Arrange cria dois users
    $user = User::factory()->create();
    $followedUser = User::factory()->create();

    //usuario segue o outro usuario
    $user->following()->create([
        'following_id' => $followedUser->id,
    ]);

    //cria um chirp do usuario que ele seguiu
    Chirp::factory()->create([
        'user_id' => $followedUser->id,
        'message' => 'Chirp de alguém que eu sigo',
    ]);

    // Act redireciona para o feed do usuario autenticado
    $response = $this->actingAs($user)->get('/');

    // Assert verifica redirecionamento e se o chirp do que ele seguiu esta la
    $response->assertOk();
    $response->assertSeeText('Chirp de alguém que eu sigo');
}


//usuário não deve visualizar Chirps de quem não segue
public function test_user_cannot_see_chirps_from_users_they_do_not_follow(): void
{
    // Arrange cria dois users
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    //cria um chirp de um deles
    Chirp::factory()->create([
        'user_id' => $otherUser->id,
        'message' => 'Este Chirp não deve aparecer',
    ]);

    // Act o outro usuario autenticado faz login
    $response = $this->actingAs($user)->get('/');

    // Assert verifica redirecionamento e se ele consegue visualizar chirp do usuario que nao segue
    $response->assertOk();
    $response->assertDontSeeText('Este Chirp não deve aparecer');
}

//visitante consegue visualizar todos os chirps
public function test_guest_can_see_all_chirps(): void
{
    // Arrange cria dois usuarios
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    //cria chirp dos dois usuarios
    Chirp::factory()->create([
        'user_id' => $user1->id,
        'message' => 'Chirp do primeiro usuário',
    ]);
    Chirp::factory()->create([
        'user_id' => $user2->id,
        'message' => 'Chirp do segundo usuário',
    ]);

    // Act redireciona para home sem ninguem estar logado
    $response = $this->get('/');

    // Assert verifica redirecionamento
    $response->assertOk();

    //verifica se da para visualizar chirps
    $response->assertSeeText('Chirp do primeiro usuário');
    $response->assertSeeText('Chirp do segundo usuário');
}


}
