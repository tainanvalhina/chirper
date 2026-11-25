<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
  
    public function index()
    {
        // Recupera o usuário autenticado.
        $user = auth()->user();
    
        // Inicia a consulta dos Chirps.
        $query = Chirp::with('user')
            ->withCount('likes');
    
        // Se existir um usuário autenticado...
        if ($user) {
    
            // Busca os próprios Chirps e os Chirps de quem ele segue.
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn(
                        'user_id',
                        $user->following()->select('following_id')
                    );
            });
    
            // Verifica se o usuário autenticado curtiu cada Chirp.
            $query->withExists([
                'likes as user_liked' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ]);
        }
    
        // Ordena pelas publicações mais recentes e pagina os resultados.
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