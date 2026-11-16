<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    public function index()
    {
        //Busca os chirps e também carrega os usuários relacionados.
        $chirps = Chirp::with('user')
        //Ordena os chirps do mais recente para o mais antigo, utilizando created_at como referência.
            ->latest()
            ->take(50)  // Limit to 50 most recent chirps
            ->get();

        return view('home', ['chirps' => $chirps]);
    }


public function store(Request $request)
{
    // Validate the request
    $validated = $request->validate([
        'message' => 'required|string|max:255',
    ], [
        'message.required' => 'Please write something to chirp!',
        'message.max' => 'Chirps must be 255 characters or less.',
    ]);

    // Create the chirp
    Chirp::create([
        'message' => $validated['message'],
    ]);

    // Redirect back to the feed
    return redirect('/')->with(
        'success',
        'Your chirp has been posted!'
    );
}

}