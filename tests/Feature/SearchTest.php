<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    //usuario consegue procurar outros usuarios por nome
    public function test_user_can_search_for_other_users_by_name(): void
    {
        //cria um usuario
        $user = User::factory()->create([
            'name' => 'Maria Silva',
        ]);

        //procura pelo nome
        $response = $this->get(route('search', [
            'search' => 'Maria',
        ]));

        //verifica se a página respondeu com status HTTP 200 e se o nome maria silva aparece
        $response->assertOk();
        $response->assertSeeText('Maria Silva');
    }

    //usuario consegue buscar chirps pelo conteudo
    public function test_user_can_search_for_chirps_by_message(): void
{

//cria usuario
    $user = User::factory()->create();

    //cria chirp do usuario
    $user->chirps()->create([
        'message' => 'Estou aprendendo Laravel!',
    ]);

    //procurando chirp criado pelo conteudo
    $response = $this->get(route('search', [
        'search' => 'Laravel',
    ]));

   //verifica se a página respondeu com status HTTP 200 e se o chirp aparece
    $response->assertOk();
    $response->assertSeeText('Estou aprendendo Laravel!');
}

//pesquisa nao pode mostrar resultados diferentes
public function test_search_does_not_show_unrelated_users_or_chirps(): void
{
    //cria usuario e chirp
    $user = User::factory()->create([
        'name' => 'Maria Silva',
    ]);
    $user->chirps()->create([
        'message' => 'Estou aprendendo Laravel!',
    ]);

    //pesquisa que nao corresponde a nenhum dos dois
    $response = $this->get(route('search', [
        'search' => 'Python',
    ]));


    //Verificamos que os resultados não exibem a usuária nem a publicação.
    $response->assertOk();
    $response->assertDontSeeText('Maria Silva');
    $response->assertDontSeeText('Estou aprendendo Laravel!');
}

//visitantes nao autenticados podem pesquisar
public function test_guest_can_search_for_users_and_chirps(): void
{

    //cria user e chirp
    $user = User::factory()->create([
        'name' => 'João Silva',
    ]);

    $user->chirps()->create([
        'message' => 'Meu primeiro Chirp!',
    ]);


    //visitante tenta pesquisar
    $response = $this->get(route('search', [
        'search' => 'João',
    ]));

    //verifica se a página respondeu com status HTTP 200 e se o usuario aparece
    $response->assertOk();
    $response->assertSeeText('João Silva');
}
}