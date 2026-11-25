<x-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold mb-4">
            Publicação
        </h1>

        {{-- Exibe a publicação utilizando o componente existente --}}
        <x-chirp :chirp="$chirp" />

        <div class="mt-6">
            <a href="{{ url()->previous() }}" class="btn btn-ghost btn-sm">
                ← Voltar
            </a>
        </div>
    </div>
</x-layout>