 
<x-layout>
    <x-slot:title>
        Editar perfil
    </x-slot:title>

    <div class="max-w-2xl mx-auto mt-8">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h1 class="text-2xl font-bold">Editar perfil</h1>

                <form
    method="POST"
    action="{{ route('profile.update', $user) }}"
    enctype="multipart/form-data"
>
                    @csrf
                    @method('PUT')

                    <div class="form-control mb-4">
                        <label class="label" for="name">
                            <span class="label-text">Nome</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="input input-bordered w-full"
                            required
                        >

                        @error('name')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mb-4">
                        <label class="label" for="bio">
                            <span class="label-text">Biografia</span>
                        </label>

                        <textarea
                            id="bio"
                            name="bio"
                            class="textarea textarea-bordered w-full"
                            rows="4"
                            maxlength="255"
                        >{{ old('bio', $user->bio) }}</textarea>

                        @error('bio')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control mb-4">
    <label class="label" for="avatar">
        <span class="label-text">Foto de perfil</span>
    </label>

    <input
        type="file"
        id="avatar"
        name="avatar"
        accept="image/*"
        class="file-input file-input-bordered w-full"
    >

    @error('avatar')
        <span class="text-error text-sm">{{ $message }}</span>
    @enderror
</div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('profile.show', $user) }}" class="btn btn-ghost">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>