<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\ChirpLiked;

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

//verificar se a primeira página exibe apenas 10 Chirps.
public function test_feed_displays_only_10_chirps_per_page(): void
{
    $user = User::factory()->create();

//cria 15 registros de chirp
    Chirp::factory()->count(15)->create([
        'user_id' => $user->id,
    ]);

    //faz uma requisição para a página inicial
    $response = $this->get('/');

    //verifica se pagina carregou
    $response->assertOk();

    //verifica os dados enviados para a view
    $response->assertViewHas('chirps', function ($chirps) {
        //Verifica se a página atual contém exatamente 10 publicações e se o total é de 15
        return $chirps->count() === 10
            && $chirps->total() === 15;
    });
}

//verificar se a segunda página exibe os 5 Chirps restantes.
public function test_feed_displays_remaining_chirps_on_second_page(): void
{
    $user = User::factory()->create();

//cria 15 registros de chirp
    Chirp::factory()->count(15)->create([
        'user_id' => $user->id,
    ]);

//acessar a segunda página
    $response = $this->get('/?page=2');

//verifica se pagina carregou
    $response->assertOk();

 //verifica os dados enviados para a view
    $response->assertViewHas('chirps', function ($chirps) {
        //Verifica se a página atual contém exatamente 5 publicações e se o total é de 15
        return $chirps->count() === 5
            && $chirps->total() === 15;
    });
}

//verifica se autor de uma publicação recebe uma notificação quando outra pessoa curte o Chirp dele
public function test_author_receives_notification_when_someone_likes_their_chirp(): void
{
    // Arrange: cria o autor e quem realizará a curtida.
    $author = User::factory()->create();
    $liker = User::factory()->create();

    // Cria um Chirp pertencente ao autor.
    $chirp = $author->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);

    // Act: outro usuário curte a publicação.
    $this->actingAs($liker)
        ->post(route('chirps.like', $chirp));

    // Assert: verifica se a notificação foi salva no banco.
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $author->id,
        'notifiable_type' => User::class,
        'type' => ChirpLiked::class,
    ]);
}


//se usuario tentar curtir novamente uma publicação que já curtiu, o sistema não crie outra notificação.
public function test_liking_the_same_chirp_twice_does_not_create_duplicate_notifications(): void
{
    $author = User::factory()->create();
    $liker = User::factory()->create();

    $chirp = $author->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);


    //usuario autenticado curte chirp de outro usuario
    $this->actingAs($liker)
        ->post(route('chirps.like', $chirp));

        //curte novamente
    $this->post(route('chirps.like', $chirp));

    //verifica se gerou apenas uma notificacao
    $this->assertDatabaseCount('notifications', 1);
}


//usuario nao deve receber notificacao de sua propria curtida
public function test_user_does_not_receive_notification_when_liking_own_chirp(): void
{
    // Arrange: cria um usuário.
    $user = User::factory()->create();

    // Cria um Chirp pertencente ao próprio usuário.
    $chirp = $user->chirps()->create([
        'message' => 'Meu próprio Chirp!',
    ]);

    // Act: o autor curte sua própria publicação.
    $this->actingAs($user)
        ->post(route('chirps.like', $chirp));

    // Assert: nenhuma notificação deve ser criada.
    $this->assertDatabaseCount('notifications', 0);
}



}