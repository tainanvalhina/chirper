<x-layout>
    <x-slot:title>
        Seguindo de {{ $user->name }}
    </x-slot:title>

    <div class="max-w-2xl mx-auto mt-8">

        <div class="card bg-base-100 shadow">
            <div class="card-body">

                <h1 class="text-xl font-semibold mb-6">
                    {{ $user->name }} está seguindo
                </h1>

                @forelse ($follows as $follow)
                    <div class="flex items-center gap-3 border-b py-4">

                        @if ($follow->following->avatar)
                            <img
                                src="{{ asset('storage/' . $follow->following->avatar) }}"
                                alt="Foto de {{ $follow->following->name }}"
                                class="w-12 h-12 rounded-full object-cover"
                            >
                        @else
                            <div class="avatar placeholder">
                                <div class="bg-neutral text-neutral-content rounded-full w-12">
                                    <span>
                                        {{ strtoupper(substr($follow->following->name, 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div>
                            <a
                                href="{{ route('profile.show', $follow->following) }}"
                                class="font-semibold hover:underline"
                            >
                                {{ $follow->following->name }}
                            </a>

                            <p class="text-sm text-base-content/60">
                                {{ $follow->following->bio ?? 'Nenhuma biografia cadastrada.' }}
                            </p>
                        </div>

                    </div>
                @empty
                    <p class="text-base-content/60">
                        Este usuário ainda não está seguindo ninguém.
                    </p>
                @endforelse

            </div>
        </div>

    </div>
</x-layout>