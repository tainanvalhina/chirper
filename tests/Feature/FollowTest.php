<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    //usuario autenticado pode seguir alguem
    public function test_authenticated_user_can_follow_another_user(): void
    {

        //cria dois usuarios
        $follower = User::factory()->create();
        $following = User::factory()->create();
    

        //um usuario ja autenticado tenta seguir o outro
        $response = $this->actingAs($follower)
            ->post(route('users.follow', $following));
    
            //verifica redirecionamento
        $response->assertRedirect();
    
        //verifica se o relacionamento foi realmente salvo no banco.
        $this->assertDatabaseHas('follows', [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }
    
    // usuário autenticado consegue deixar de seguir outro usuário
    public function test_authenticated_user_can_unfollow_another_user(): void
{
    // Arrange: preparar os usuários
    $follower = User::factory()->create();
    $following = User::factory()->create();

    // Criar o relacionamento antes do teste
    $follower->following()->create([
        'following_id' => $following->id,
    ]);

    // Act: solicitar deixar de seguir
    $response = $this->actingAs($follower)
        ->delete(route('users.unfollow', $following));

    // Assert: verificar o resultado
    $response->assertRedirect();

    //verifica se o relacionamento foi realmente removido do banco.
    $this->assertDatabaseMissing('follows', [
        'follower_id' => $follower->id,
        'following_id' => $following->id,
    ]);
}


//um usuario nao pode seguir a si mesmo
public function test_user_cannot_follow_themselves(): void
{
    // Arrange
    $user = User::factory()->create();

    // Act tentando seguir a si mesmo
    $response = $this->actingAs($user)
        ->post(route('users.follow', $user));

    // Assert verifica se a aplicação retornou o status HTTP 403 (Forbidden)
    $response->assertForbidden();

    //verifica se nenhum relacionamento foi criado
    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'following_id' => $user->id,
    ]);
}


//usuario nao pode seguir outro usuario duas vezes
public function test_user_cannot_follow_the_same_user_twice(): void
{
    // Arrange
    $follower = User::factory()->create();
    $following = User::factory()->create();

    // Act: seguir duas vezes
    $this->actingAs($follower)
        ->post(route('users.follow', $following));

    $this->post(route('users.follow', $following));

    // Assert: verifica se ha apenas um relacionamento
    $this->assertDatabaseCount('follows', 1);
}

//um visitante não autenticado não pode seguir outro usuário.
public function test_guest_cannot_follow_another_user(): void
{
    // Arrange
    $user = User::factory()->create();

    // Act: tentar seguir sem estar autenticado
    $response = $this->post(route('users.follow', $user));

    // Assert verifica se redirecionou para login
    $response->assertRedirect(route('login'));

    //verifica se nenhum relacionamento foi criado para esse usuário como destino.
    $this->assertDatabaseMissing('follows', [
        'following_id' => $user->id,
    ]);
}

//verifica se a contagem esta certa
public function test_user_followers_and_following_counts_are_correct(): void
{
    // Arrange
    $user = User::factory()->create();
    $followerOne = User::factory()->create();
    $followerTwo = User::factory()->create();
    $followingUser = User::factory()->create();

    // Act duas pessoas seguem user e user segue uma pessoa
    $followerOne->following()->create([
        'following_id' => $user->id,
    ]);

    $followerTwo->following()->create([
        'following_id' => $user->id,
    ]);

    $user->following()->create([
        'following_id' => $followingUser->id,
    ]);

    // Assert verifica se followers = 2 e following = 1
    $this->assertEquals(2, $user->followers()->count());
    $this->assertEquals(1, $user->following()->count());
}

//usuario pode ver seus seguidores
public function test_user_can_view_their_followers_list(): void
{
    // Arrange cria dois usuarios
    $user = User::factory()->create();
    $follower = User::factory()->create();


    //o usuario follower pede para seguir o usuario user
    $follower->following()->create([
        'following_id' => $user->id,
    ]);


    // Act usuario user pede para ver seus seguidores
    $response = $this->actingAs($user)
        ->get(route('users.followers', $user));

    // Assert verifica se pagina abre com sucesso
    $response->assertOk();

    //verifica se usuario aparece na listagem
    $response->assertSeeText($follower->name);
}

//usuario consegue acessar quem ele segue
public function test_user_can_view_their_following_list(): void
{
    // Arrange cria dois usuarios
    $user = User::factory()->create();
    $following = User::factory()->create();

    //um usuario faz requisicao para seguir outro
    $user->following()->create([
        'following_id' => $following->id,
    ]);

    // Act usuario seguido pede para ver os seus seguidores
    $response = $this->actingAs($user)
        ->get(route('users.following', $user));

    // Assert verifica se pagina abriu
    $response->assertOk();

    //verifica se o usuario que ele seguiu aparece na lista
    $response->assertSeeText($following->name);
}

//teste se o follow cria notificacao
public function test_following_a_user_creates_a_notification(): void
{
    // Criamos quem vai seguir.
    $follower = User::factory()->create();

    // Criamos quem receberá o novo seguidor.
    $followed = User::factory()->create();

    // Simulamos o usuário autenticado seguindo outra pessoa.
    $this->actingAs($follower)
        ->post(route('users.follow', $followed));

    // busca a primeira notificacao recebida pelo usuário seguido.
    $notification = $followed->notifications()
        ->where('type', \App\Notifications\NewFollower::class)
        ->first();

    // Verificamos se a notificação foi criada.
    $this->assertNotNull($notification);

    // Verificamos se os dados identificam corretamente quem seguiu.
    $this->assertEquals(
        $follower->id,
        $notification->data['user_id']
    );

    $this->assertEquals(
        $follower->name,
        $notification->data['user_name']
    );
}


//garante que seguir a mesma pessoa novamente não gera notificações duplicadas.
public function test_following_the_same_user_twice_does_not_create_duplicate_notifications(): void
{
    // Criamos quem vai seguir e quem será seguido.
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    // Primeira tentativa de seguir.
    $this->actingAs($follower)
        ->post(route('users.follow', $followed));

    // Segunda tentativa de seguir a mesma pessoa.
    $this->post(route('users.follow', $followed));

    // Verificamos se existe somente uma notificação.
    $this->assertCount(
        1,
        $followed->notifications()
            ->where('type', \App\Notifications\NewFollower::class)
            ->get()
    );
}


}