<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{


    //busca automaticamente o usuario correspondente no banco
    public function show(User $user)
    {

        //busca os chirps do usuario filtrando do mais recente ao mais antigo
        $chirps = $user->chirps()
            ->latest()
            ->get();

            //envia os dados para view
        return view('profile.show', [
            'user' => $user,
            'chirps' => $chirps,
        ]);
    }

    public function edit(User $user)
{
//garantir que cada usuário só consiga editar o próprio perfil.
    abort_unless(auth()->user()->is($user), 403);

    //envia os dados do usuario para view
    return view('profile.edit', [
        'user' => $user,
    ]);
}

public function update(Request $request, User $user)
{

    //garantir que cada usuário só consiga editar o próprio perfil. se for pessoas diferentes, retorna erro.
    abort_unless(auth()->user()->is($user), 403);

    // Valida os dados recebidos
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'bio' => 'nullable|string|max:255',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    //verifica se usuario enviou arquivo, avatar é opcional
    if ($request->hasFile('avatar')) {

        // Exclui a foto antiga, caso exista
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    
        // Armazena a nova foto
        $validated['avatar'] = $request->file('avatar')
            ->store('avatars', 'public');
    }
    
    // Atualiza os dados do usuário
    $user->update($validated);

    // Redireciona para o perfil
    return redirect()
        ->route('profile.show', $user)
        ->with('success', 'Perfil atualizado com sucesso!');
}

//o que qualquer visitante consegue visualizar.
public function test_user_can_view_public_profile(): void
{
   //cria uma usuária fictícia com nome e bio.
    $user = User::factory()->create([
        'name' => 'Maria Silva',
        'bio' => 'Desenvolvedora PHP',
    ]);

    //laravel gera a URL do perfil daquele usuário e simula uma requisicao sem ninguem estar autentificado
    $response = $this->get(route('profile.show', $user));

    $response->assertOk();

    $response->assertSee('Maria Silva');
    $response->assertSee('Desenvolvedora PHP');
}
}
