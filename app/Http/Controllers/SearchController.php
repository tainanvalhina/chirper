<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chirp;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        // Recupera o termo digitado na busca.
        $search = $request->input('search');
    
        // Inicializa as consultas.
        $users = collect();
        $chirps = collect();
    
        // Só realiza a busca se existir um termo informado.
        if ($search) {
            // Busca usuários pelo nome.
            $users = User::where('name', 'LIKE', "%{$search}%")
                ->get();
    
            // Busca Chirps pelo conteúdo.
            $chirps = Chirp::with('user')
                ->where('message', 'LIKE', "%{$search}%")
                ->latest()
                ->get();
        }
    
        return view('search.index', compact('search', 'users', 'chirps'));
    }
}
