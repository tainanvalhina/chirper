<!DOCTYPE html>
<html lang="pt-BR" data-theme="laravelChirper">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - Chirper' : 'Chirper' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-base-200 font-sans">

<nav class="navbar bg-base-100">
    <div class="navbar-start">
        <a href="/" class="btn btn-ghost text-xl">🐦 Chirper</a>
    </div>

    <div class="navbar-end gap-2">
    @auth
    @php
    $user = auth()->user();

    $unreadNotificationsCount = $user
        ->unreadNotifications()
        ->count();

    $notifications = $user
        ->notifications()
        ->latest()
        ->take(5)
        ->get();
@endphp

        {{-- Sino de notificações --}}
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    @if ($unreadNotificationsCount > 0)
                        <span class="badge badge-xs badge-primary indicator-item">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </div>
            </div>

            <div tabindex="0" class="dropdown-content z-50 card card-compact bg-base-100 shadow-xl w-80 p-4">
    <p class="font-semibold mb-2">Notificações</p>

    @forelse ($notifications as $notification)
    <div class="border-b border-base-300 py-3 px-3 rounded-lg
    {{ $notification->read_at ? 'bg-white text-gray-800' : 'bg-blue-100 text-blue-900' }}">
        <form
    method="POST"
    action="{{ route('notifications.read', $notification->id) }}"
>
    @csrf


    <button type="submit" class="text-sm text-left hover:underline">
    {{ $notification->data['message'] }}
</button>

</form>
            <p class="text-xs text-base-content/60 mt-1">
                {{ $notification->created_at->diffForHumans() }}
            </p>
        </div>
    @empty
        <p class="text-sm text-base-content/60 py-3">
            Você ainda não tem notificações.
        </p>
    @endforelse

    <p class="text-xs text-base-content/60 mt-2">
        {{ $unreadNotificationsCount }} notificações não lidas
    </p>

    <div class="border-t border-base-300 mt-3 pt-3">
    <a
        href="{{ route('notifications.index') }}"
        class="btn btn-ghost btn-sm w-full"
    >
        Ver todas as notificações
    </a>
</div>
</div>
        </div>

        <span class="text-sm">{{ auth()->user()->name }}</span>

        <form method="POST" action="/logout" class="inline">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">
                Logout
            </button>
        </form>
    @else
        <a href="/login" class="btn btn-ghost btn-sm">Sign In</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign Up</a>
    @endauth
</div>
</nav>


<!-- Success Toast -->
@if (session('success'))
    <div class="toast toast-top toast-center">
        <div class="alert alert-success animate-fade-out">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-6 w-6 shrink-0 stroke-current"
                 fill="none"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

    <main class="flex-1 container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="footer footer-center p-5 bg-base-300 text-base-content text-xs">
        <div>
            <p>© {{ date('Y') }} Chirper - Built with Laravel and ❤️</p>
        </div>
    </footer>

</body>
</html>