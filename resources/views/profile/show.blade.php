<x-layout>
    <x-slot:title>
        Perfil de {{ $user->name }}
    </x-slot:title>

    <div class="max-w-2xl mx-auto mt-8">

        <div class="card bg-base-100 shadow">
            <div class="card-body">

                {{-- Informações do perfil --}}
                <div class="flex items-center gap-4">

                    @if ($user->avatar)
                        <img
                            src="{{ asset('storage/' . $user->avatar) }}"
                            alt="Foto de {{ $user->name }}"
                            class="w-20 h-20 rounded-full object-cover"
                        >
                    @else
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-20">
                                <span class="text-2xl">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ $user->name }}
                        </h1>

                        <p class="text-base-content/60">
                            {{ $user->bio ?? 'Nenhuma biografia cadastrada.' }}
                        </p>
                    </div>

                    <div class="flex gap-4 mt-2 text-sm">
    <a href="{{ route('users.followers', $user) }}" class="hover:underline">
        <strong>{{ $user->followers()->count() }}</strong>
        Seguidores
    </a>

    <a href="{{ route('users.following', $user) }}" class="hover:underline">
        <strong>{{ $user->following()->count() }}</strong>
        Seguindo
    </a>
</div>
                    
                </div>

                @if (auth()->id() === $user->id)
    <a href="{{ route('profile.edit', $user) }}"
       class="btn btn-primary btn-sm mt-3">
        Editar perfil
    </a>
@endif
            

@auth
    @if (auth()->id() !== $user->id)
        @php
            $isFollowing = $user->followers()
                ->where('follower_id', auth()->id())
                ->exists();
        @endphp

        @if ($isFollowing)
            <form method="POST" action="{{ route('users.unfollow', $user) }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-outline btn-sm mt-3">
                    Deixar de seguir
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('users.follow', $user) }}">
                @csrf

                <button type="submit" class="btn btn-primary btn-sm mt-3">
                    Seguir
                </button>
            </form>
        @endif
    @endif
@endauth

                <div class="divider"></div>

                {{-- Publicações do usuário --}}
                <h2 class="text-xl font-semibold">
                    Publicações
                </h2>

                @forelse ($chirps as $chirp)
                    <div class="border-b py-4">
                        <p>{{ $chirp->message }}</p>

                        <span class="text-sm text-gray-500">
                            {{ $chirp->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">
                        Este usuário ainda não publicou nenhum Chirp.
                    </p>
                @endforelse

            </div>
        </div>

    </div>
</x-layout>