<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
  
public function index()
{
    // Inicia a consulta dos Chirps.
    // with('user') carrega os usuários relacionados antecipadamente,
    // evitando uma consulta ao banco para cada autor exibido no feed.
    $query = Chirp::with('user')
        // calcula a quantidade de curtidas de cada Chirp.
        ->withCount('likes');
    // Verifica se existe um usuário autenticado.
    if (auth()->check()) {
        // Verifica se o usuário logado curtiu cada publicação.
        $query->withExists([
            'likes as user_liked' => function ($query) {
                // Considera apenas as curtidas feitas pelo usuário atual.
                $query->where('user_id', auth()->id());
            }
        ]);
    }
    // take(50) limita o feed às 50 publicações mais recentes.
    $chirps = $query
        ->latest()
        ->paginate(10);

        
    return view('home', ['chirps' => $chirps]);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $request->user()->chirps()->create($validated);

        return redirect('/')->with('success', 'Your chirp has been posted!');
    }

    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', compact('chirp'));
    }

    public function update(Request $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect('/')->with('success', 'Chirp updated!');
    }

    public function destroy(Chirp $chirp)
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect('/')->with('success', 'Chirp deleted!');
    }
}