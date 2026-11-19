<?php

namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_edit_own_profile(): void
    {
        //cria usuario ficticio
        $user = User::factory()->create();

        //simula autentificacao e acesso a rota de edicao
        $response = $this->actingAs($user)
            ->get(route('profile.edit', $user));

            //Verifica se recebeu HTTP 200
        $response->assertOk();

        //verifica se texto aparece na página
        $response->assertSee('Editar perfil');
    }

    public function test_user_cannot_access_another_users_edit_profile(): void
{
    //cria dois usuarios
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    //usuario tenta acessar a edicao de perfil de outro usuario
    $response = $this->actingAs($user)
        ->get(route('profile.edit', $anotherUser));

        //verifica se houve erro http 403
    $response->assertForbidden();
}

//mesmo que alguém tente enviar uma requisição diretamente, não pode alterar os dados de outro usuário
public function test_user_cannot_update_another_users_profile(): void
{
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    //guarda o nome original de anotherUser
    $originalName = $anotherUser->name;

    //usuario user autentificado tenta alterar o perfil de anotherUser
    $response = $this->actingAs($user)
        ->put(route('profile.update', $anotherUser), [
            'name' => 'Nome alterado',
            'bio' => 'Biografia alterada',
        ]);

        //verifica se houve bloqueio
    $response->assertForbidden();


//verifica se o dado nome continua igual ao antigo
    $this->assertDatabaseHas('users', [
        'id' => $anotherUser->id,
        'name' => $originalName,
    ]);
}


public function test_user_can_update_own_profile(): void
{
    $user = User::factory()->create();

//usuario tenta editar seu proprio perfil
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => 'Novo Nome',
            'bio' => 'Minha nova biografia',
        ]);
//Verifica se foi redirecionado para o próprio perfil
    $response->assertRedirect(route('profile.show', $user));

    //verifica se houve alteracao no banco
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Novo Nome',
        'bio' => 'Minha nova biografia',
    ]);
}

//teste de nome obrigatorio

public function test_name_is_required_to_update_profile(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => '',
            'bio' => 'Minha biografia',
        ]);

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
    ]);
}

//nao pode passar de 255 caracteres a biografia do nome

public function test_bio_cannot_exceed_255_characters(): void
{
    $user = User::factory()->create();

    //str_repeat('a', 256) -> repete o a 256x
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => str_repeat('a', 256),
        ]);

        //verifica se o Laravel identificou o erro no campo bio.
    $response->assertSessionHasErrors('bio');


//confere se os dados originais continuam no banco.
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
        'bio' => $user->bio,
    ]);
}

public function test_user_can_upload_profile_avatar(): void
{
    //Cria um armazenamento falso para os testes. Assim, não precisamos mexer nos arquivos reais do projeto.
    Storage::fake('public');

    $user = User::factory()->create();

    //Cria uma imagem fictícia para simular o upload.
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $avatar,
        ]);

    $response->assertRedirect(route('profile.show', $user));

    //Atualiza os dados do objeto $user consultando novamente o banco.
    $user->refresh();

    //verifica se o campo avatar recebeu algum valor.
    $this->assertNotNull($user->avatar);


    //confirma que o arquivo realmente foi armazenado no disco simulado.
    Storage::disk('public')->assertExists($user->avatar);
}


//impedir o envio de arquivos que não sejam imagens
public function test_user_cannot_upload_invalid_avatar(): void
{
    Storage::fake('public');
    
    //cria usuário fictício no banco de dados.
    $user = User::factory()->create();

    //simulando um documento PDF de 100 KB.
    $file = UploadedFile::fake()->create(
        'document.pdf',
        100,
        'application/pdf'
    );

    //simula o envio do formulario
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $file,
        ]);

        //verifica se houve erro
    $response->assertSessionHasErrors('avatar');

    //verifica o campo esta null no banco
    $this->assertNull($user->fresh()->avatar);

    //verifica se usuario continua no banco
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
    ]);
}

public function test_invalid_avatar_does_not_replace_existing_avatar(): void
{
    Storage::fake('public');

    // Cria um usuário que já possui uma foto
    $user = User::factory()->create([
        'avatar' => 'avatars/foto-antiga.jpg',
    ]);

    // Simula a existência da foto antiga no armazenamento
    Storage::disk('public')->put(
        'avatars/foto-antiga.jpg',
        'conteudo da imagem antiga'
    );

    // Cria um arquivo PDF inválido para o campo avatar
    $file = UploadedFile::fake()->create(
        'document.pdf',
        100,
        'application/pdf'
    );

    // Tenta atualizar o perfil enviando o PDF
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $file,
        ]);

    // Verifica se a validação rejeitou o arquivo
    $response->assertSessionHasErrors('avatar');

    // Verifica se o caminho da foto antiga continua no banco
    $this->assertEquals(
        'avatars/foto-antiga.jpg',
        $user->fresh()->avatar
    );

    // Verifica se o arquivo antigo continua armazenado
    Storage::disk('public')->assertExists('avatars/foto-antiga.jpg');
}


//o usuário já tem uma foto e consegue substituí-la por outra imagem válida
public function test_user_can_replace_existing_avatar(): void
{
    Storage::fake('public');

    // Cria usuário com foto antiga
    $user = User::factory()->create([
        'avatar' => 'avatars/foto-antiga.jpg',
    ]);

    // Simula a foto antiga no armazenamento
    Storage::disk('public')->put(
        'avatars/foto-antiga.jpg',
        'conteudo da imagem antiga'
    );

    // Cria uma nova imagem válida
    $newAvatar = UploadedFile::fake()->image('nova-foto.jpg');

    // Envia a atualização do perfil
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $newAvatar,
        ]);

    $response->assertRedirect(route('profile.show', $user));

    // Atualiza os dados do usuário a partir do banco
    $user->refresh();

    // Verifica se o caminho da imagem mudou
    $this->assertNotEquals(
        'avatars/foto-antiga.jpg',
        $user->avatar
    );

    // Verifica se a nova imagem foi salva
    Storage::disk('public')->assertExists($user->avatar);

    // Verifica se a imagem antiga foi excluída
    Storage::disk('public')->assertMissing('avatars/foto-antiga.jpg');
}

public function test_user_cannot_upload_avatar_larger_than_2mb(): void
{
    Storage::fake('public');

    $user = User::factory()->create();

    // Cria uma imagem fictícia de 3 MB
    $avatar = UploadedFile::fake()->image('avatar.jpg')->size(3072);

    // Tenta atualizar o perfil
    $response = $this->actingAs($user)
        ->put(route('profile.update', $user), [
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $avatar,
        ]);

    // Verifica se o Laravel rejeitou a imagem
    $response->assertSessionHasErrors('avatar');

    // Verifica se nenhum avatar foi salvo no banco
    $this->assertNull($user->fresh()->avatar);

    // Verifica se nenhum arquivo foi armazenado
    Storage::disk('public')->assertDirectoryEmpty('avatars');
}
}
