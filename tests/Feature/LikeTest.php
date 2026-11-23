<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;


    //verifica se usuario logado consegue curtir um chirp
    public function test_authenticated_user_can_like_a_chirp(): void
    {
        // Arrange: cria um usuário e um Chirp
        $user = User::factory()->create();

        $chirp = $user->chirps()->create([
            'message' => 'Meu primeiro Chirp!',
        ]);

        // Act: usuário realiza a curtida
        $response = $this->actingAs($user)
            ->post(route('chirps.like', $chirp));

        // Assert: verifica se a curtida foi salva
        $response->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'chirp_id' => $chirp->id,
        ]);
    }


 //verifica se usuario nao consegue curtir um chirp duas vezes

 public function test_user_cannot_like_the_same_chirp_twice(): void
{
  // Arrange: cria um usuário e um Chirp
    $user = User::factory()->create();

    $chirp = $user->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // usuario ao se logar tenta curtir pela primeira vez
    $this->actingAs($user)
        ->post(route('chirps.like', $chirp));


//tenta curtir novamente, nao ha necessidade de autenticar de novo pois ele ja esta logado
    $this->post(route('chirps.like', $chirp));

    // verifica quantidade de registro esperada
    $this->assertDatabaseCount('likes', 1);
}

//usuario consegue remover curtida que ja realizou
public function test_user_can_unlike_a_chirp(): void
{
    // Arrange: prepara o usuário e o Chirp
    $user = User::factory()->create();

    $chirp = $user->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // Cria uma curtida para o usuário
    $chirp->likes()->create([
        'user_id' => $user->id,
    ]);

    // Act: usuário remove a curtida
    $response = $this->actingAs($user)
        ->delete(route('chirps.unlike', $chirp));

    // Assert: verifica se foi redirecionado
    $response->assertRedirect();

    // Verifica se a curtida não existe mais
    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'chirp_id' => $chirp->id,
    ]);
}

//visitante nao autenticado nao pode curtir
public function test_guest_cannot_like_a_chirp(): void
{
    // Arrange: cria um usuário e um Chirp
    $user = User::factory()->create();

    $chirp = $user->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // Act: visitante tenta curtir sem estar logado
    $response = $this->post(route('chirps.like', $chirp));

    // Assert: deve ser redirecionado para o login
    $response->assertRedirect(route('login'));

    // Verifica que nenhuma curtida foi criada
    $this->assertDatabaseCount('likes', 0);
}

public function test_chirp_displays_likes_count(): void
{
    // Arrange: cria três usuários
    $author = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Cria um Chirp
    $chirp = $author->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // Cria duas curtidas
    $chirp->likes()->create([
        'user_id' => $user1->id,
    ]);

    $chirp->likes()->create([
        'user_id' => $user2->id,
    ]);

    // Act: acessa a página inicial
    $response = $this->actingAs($author)->get('/');

    // Assert: verifica se a quantidade aparece na tela
    $response->assertOk();
    $response->assertSeeText('2');
    $response->assertSeeText('curtidas');
}


public function test_different_users_can_like_the_same_chirp(): void
{
    // Arrange: cria usuários
    $author = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Cria um Chirp
    $chirp = $author->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // Act: os dois usuários curtem a publicação
    $this->actingAs($user1)
        ->post(route('chirps.like', $chirp));

    $this->actingAs($user2)
        ->post(route('chirps.like', $chirp));

    // Assert: devem existir duas curtidas
    $this->assertDatabaseCount('likes', 2);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user1->id,
        'chirp_id' => $chirp->id,
    ]);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user2->id,
        'chirp_id' => $chirp->id,
    ]);
}


public function test_authenticated_user_can_see_if_they_liked_a_chirp(): void
{
    // Cria um usuário para realizar os testes.
    $user = User::factory()->create();

    // Cria dois Chirps pertencentes ao usuário.
    $likedChirp = Chirp::factory()->create([
        'user_id' => $user->id,
    ]);
    
    $notLikedChirp = Chirp::factory()->create([
        'user_id' => $user->id,
    ]);

    // Registra uma curtida no primeiro Chirp.
    $likedChirp->likes()->create([
        'user_id' => $user->id,
    ]);

    // Acessa a página inicial como usuário autenticado.
    $response = $this->actingAs($user)->get('/');

    // Verifica se a página carregou corretamente.
    $response->assertOk();

    // Verifica o valor de user_liked nos Chirps enviados à view.
    $response->assertViewHas('chirps', function ($chirps) use ($likedChirp, $notLikedChirp) {

        $liked = $chirps->firstWhere('id', $likedChirp->id);
        $notLiked = $chirps->firstWhere('id', $notLikedChirp->id);

        return (bool) $liked->user_liked === true
            && (bool) $notLiked->user_liked === false;
    });
}

public function test_guest_can_view_chirps_without_errors(): void
{
    // Cria um usuário e uma publicação.
    $user = User::factory()->create();
    $chirp = Chirp::factory()->create([
        'user_id' => $user->id,
    ]);

    // Acessa a página inicial sem estar autenticado.
    $response = $this->get('/');

    // Verifica se a página carregou corretamente.
    $response->assertOk();

    // Verifica se a publicação aparece no feed.
    $response->assertSeeText($chirp->message);
}

}