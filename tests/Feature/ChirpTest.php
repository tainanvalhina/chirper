<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    //prepara o banco para o teste
    use RefreshDatabase;

    public function test_home_page_displays_chirps(): void
    {

        //simula uma requisição HTTP GET para a página inicial.
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    //testa se usuario logado consegue publicar
    public function test_authenticated_user_can_create_a_chirp(): void
    {

        //Cria um usuário fictício no banco de dados para o teste.
        $user = User::factory()->create();

        //Simula que esse usuário está autenticado e envia um formulario
        $response = $this
            ->actingAs($user)
            ->post('/chirps', [
                'message' => 'Meu primeiro Chirp automatizado!',
            ]);

        //Verifica se o sistema redirecionou para a página inicial.

        $response->assertRedirect('/');

        //Verifica se determinado registro existe no banco de dados.
        $this->assertDatabaseHas('chirps', [
            'user_id' => $user->id,
            'message' => 'Meu primeiro Chirp automatizado!',
        ]);
    }

//verificar que nao logado nao pode criar chirp
    public function test_guest_cannot_create_a_chirp(): void
{
    //simula um visitante enviando uma publicação.
    $response = $this->post('/chirps', [
        'message' => 'Tentativa de publicação sem login!',
    ]);

    //verifica se o middleware auth redirecionou o visitante para a tela de login.
    $response->assertRedirect('/login');
    
    //garante que nenhuma publicação foi criada.
    $this->assertDatabaseCount('chirps', 0);
}


public function test_user_cannot_update_another_users_chirp(): void
{
    // Criamos dois usuários diferentes
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();

    // O primeiro usuário cria um Chirp
    $chirp = $owner->chirps()->create([
        'message' => 'Mensagem original',
    ]);

    // O segundo usuário tenta editar esse Chirp
    $response = $this
        ->actingAs($anotherUser)
        ->put("/chirps/{$chirp->id}", [
            'message' => 'Mensagem alterada',
        ]);

    // Esperamos que o acesso seja negado
    $response->assertForbidden();

    // A mensagem original deve continuar no banco
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Mensagem original',
    ]);
}

public function test_user_cannot_delete_another_users_chirp(): void
{
    // Criamos dois usuários diferentes
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    // O primeiro usuário cria um Chirp
    $chirp = $owner->chirps()->create([
        'message' => 'Mensagem que não deve ser excluída',
    ]);

    //o segundo tenta excluir
    $response = $this
        ->actingAs($anotherUser)
        ->delete("/chirps/{$chirp->id}");

           // Esperamos que o acesso seja negado
    $response->assertForbidden();

    //verifica se continua no banco
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Mensagem que não deve ser excluída',
    ]);
}

//chirp nao pode ser vazio
public function test_chirp_message_is_required(): void
{
    //cria usuario
    $user = User::factory()->create();

    //tenta enviar mensagem vazia
    $response = $this
        ->actingAs($user)
        ->post('/chirps', [
            'message' => '',
        ]);

    $response->assertSessionHasErrors('message');

    $this->assertDatabaseCount('chirps', 0);
}


//chirp nao pode ser atualizado vazio
public function test_chirp_cannot_be_updated_with_an_empty_message(): void
{
    $user = User::factory()->create();

    $chirp = $user->chirps()->create([
        'message' => 'Mensagem original',
    ]);

    $response = $this
        ->actingAs($user)
        ->put("/chirps/{$chirp->id}", [
            'message' => '',
        ]);

    $response->assertSessionHasErrors('message');

    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Mensagem original',
    ]);
}

//chirp nao ter mais de 255 caracteres
public function test_chirp_message_cannot_exceed_255_characters(): void
{
    //cria usuario
    $user = User::factory()->create();

    //envia uma mensagem com mais de 255 caracteres. Gera uma string com 256 letras 'a'
    $response = $this
        ->actingAs($user)
        ->post('/chirps', [
            'message' => str_repeat('a', 256),
        ]);

    $response->assertSessionHasErrors('message');

    $this->assertDatabaseCount('chirps', 0);
}

//chirp nao ter mais de 255 caracteres no update

public function test_chirp_cannot_be_updated_with_more_than_255_characters(): void
{
    $user = User::factory()->create();

    //cria chirp
    $chirp = $user->chirps()->create([
        'message' => 'Mensagem original',
    ]);

    //requisita um update com mais caracteres que 255
    $response = $this
        ->actingAs($user)
        ->put("/chirps/{$chirp->id}", [
            'message' => str_repeat('a', 256),
        ]);

        //verifica erro
    $response->assertSessionHasErrors('message');

    //verifica se a mensagem original continua no banco
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Mensagem original',
    ]);
}

//teste se o dono do chirp consegue editar o seu proprio chirp
public function test_user_can_update_own_chirp(): void
{
//cria usuario
    $user = User::factory()->create();

    //gera um chirp
    $chirp = $user->chirps()->create([
        'message' => 'Mensagem original',
    ]);

    //usuario dono do chirp faz requisicao de edit do seu proprio chirp
    $response = $this
        ->actingAs($user)
        ->put("/chirps/{$chirp->id}", [
            'message' => 'Mensagem atualizada',
        ]);

        //verifica se houve redirecionamento para a página inicial
    $response->assertRedirect('/');

//verifica se atualizou no banco
    $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'message' => 'Mensagem atualizada',
    ]);
}

public function test_user_can_delete_own_chirp(): void
{
//cria usuario
    $user = User::factory()->create();

    //gera um chirp
    $chirp = $user->chirps()->create([
        'message' => 'Mensagem que será excluida',
    ]);

    //usuario dono do chirp faz requisicao de edit do seu proprio chirp
    $response = $this
        ->actingAs($user)
        ->delete("/chirps/{$chirp->id}");

        //verifica se houve redirecionamento para a página inicial
    $response->assertRedirect('/');

//verifica se o registro realmente não existe mais no banco de dados.

$this->assertDatabaseMissing('chirps', [
    'id' => $chirp->id,
]);

}

//verificar se os chirps tem todas as infos
public function test_home_page_displays_chirp_message_and_author(): void
{
    //cria usuario
    $user = User::factory()->create([
        'name' => 'Maria Teste',
    ]);

    //usuario cria novo chirp
    $user->chirps()->create([
        'message' => 'Aprendendo testes com Laravel!',
    ]);

    //verifica redirecionamento
    $response = $this->get('/');

//verifica se a resposta foi HTTP 200.
    $response->assertOk();
    
    //verifica se nome aparece no HTML retornado.
    $response->assertSee('Maria Teste');

    //verifica se mensagem aparece no HTML retornado.
    $response->assertSee('Aprendendo testes com Laravel!');
}

}