<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Exibe o histórico de notificações do usuário autenticado.
     */
    public function index(Request $request): View
    {
        // Busca todas as notificações do usuário autenticado,
        // começando pelas mais recentes, com 10 por página.
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }
}