<x-layout>
    <x-slot:title>
        Buscar
    </x-slot:title>

    <div class="max-w-3xl mx-auto mt-8">
        <div class="card bg-base-100 shadow">
            <div class="card-body">

                <h1 class="text-2xl font-bold mb-4">
                    Buscar usuários e publicações
                </h1>

                {{-- Formulário de busca --}}
                <form action="{{ route('search') }}" method="GET">
                    <div class="flex gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Digite o que deseja encontrar..."
                            class="input input-bordered w-full"
                        >

                        <button type="submit" class="btn btn-primary">
                            Buscar
                        </button>
                    </div>
                </form>

                @if (!$search)
    <p class="text-base-content/60 mt-6">
        Digite um nome ou uma palavra para encontrar usuários e publicações.
    </p>
@endif

                @if ($search)

                    {{-- Usuários encontrados --}}
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold mb-4">
                            Usuários encontrados
                        </h2>

                        @forelse ($users as $user)
                            <div class="flex items-center gap-3 border-b py-3">

                                @if ($user->avatar)
                                    <img
                                        src="{{ asset('storage/' . $user->avatar) }}"
                                        alt="Foto de {{ $user->name }}"
                                        class="w-12 h-12 rounded-full object-cover"
                                    >
                                @else
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-12">
                                            <span>
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <a
                                        href="{{ route('profile.show', $user) }}"
                                        class="font-semibold hover:underline"
                                    >
                                        {{ $user->name }}
                                    </a>

                                    <p class="text-sm text-base-content/60">
                                        {{ $user->bio ?? 'Nenhuma biografia cadastrada.' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-base-content/60">
                                Nenhum usuário encontrado.
                            </p>
                        @endforelse
                    </div>

                    {{-- Chirps encontrados --}}
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-4">
                            Publicações encontradas
                        </h2>

                        @forelse ($chirps as $chirp)
                            <div class="border-b py-4">

                                <a
                                    href="{{ route('profile.show', $chirp->user) }}"
                                    class="font-semibold hover:underline"
                                >
                                    {{ $chirp->user->name }}
                                </a>

                                <p class="mt-2">
                                    {{ $chirp->message }}
                                </p>

                                <p class="text-xs text-base-content/60 mt-2">
                                    {{ $chirp->created_at->diffForHumans() }}
                                </p>

                            </div>
                        @empty
                            <p class="text-base-content/60">
                                Nenhuma publicação encontrada.
                            </p>
                        @endforelse
                    </div>

                @endif

            </div>
        </div>
    </div>
</x-layout>