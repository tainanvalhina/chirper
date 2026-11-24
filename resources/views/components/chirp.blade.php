
@props(['chirp'])

{{-- Verifica a quantidade de curtidas e se o usuário logado curtiu --}}
@php
    $likesCount = $chirp->likes_count;

    $userLiked = auth()->check()
        ? $chirp->likes()->where('user_id', auth()->id())->exists()
        : false;
@endphp

<div class="card bg-base-100 shadow">
    <div class="card-body">
        <div class="flex space-x-3">
            @if ($chirp->user)
                <a
                    href="{{ route('profile.show', $chirp->user) }}"
                    class="avatar"
                >
                    <div class="size-10 rounded-full">
                        @if ($chirp->user->avatar)
                            <img
                                src="{{ asset('storage/' . $chirp->user->avatar) }}"
                                alt="{{ $chirp->user->name }}'s avatar"
                                class="rounded-full"
                            />
                        @else
                            <img
                                src="https://avatars.laravel.cloud/{{ urlencode($chirp->user->email) }}"
                                alt="{{ $chirp->user->name }}'s avatar"
                                class="rounded-full"
                            />
                        @endif
                    </div>
                </a>
            @else
                <div class="avatar placeholder">
                    <div class="size-10 rounded-full">
                        <img
                            src="https://avatars.laravel.cloud/f61123d5-0b27-434c-a4ae-c653c7fc9ed6?vibe=stealth"
                            alt="Anonymous User"
                            class="rounded-full"
                        />
                    </div>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex justify-between w-full">
                    <div class="flex items-center gap-1">
                        @if ($chirp->user)
                            <a
                                href="{{ route('profile.show', $chirp->user) }}"
                                class="text-sm font-semibold hover:text-primary transition-colors"
                            >
                                {{ $chirp->user->name }}
                            </a>
                        @else
                            <span class="text-sm font-semibold">
                                Anonymous
                            </span>
                        @endif

                        <span class="text-base-content/60">·</span>

                        <span class="text-sm text-base-content/60">
                            {{ $chirp->created_at->diffForHumans() }}
                        </span>

                        @if ($chirp->updated_at->gt($chirp->created_at->addSeconds(5)))
                            <span class="text-base-content/60">·</span>
                            <span class="text-sm text-base-content/60 italic">
                                edited
                            </span>
                        @endif
                    </div>

                    @can('update', $chirp)
                        <div class="flex gap-1">
                            <a
                                href="/chirps/{{ $chirp->id }}/edit"
                                class="btn btn-ghost btn-xs"
                            >
                                Edit
                            </a>

                            <form method="POST" action="/chirps/{{ $chirp->id }}">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this chirp?')"
                                    class="btn btn-ghost btn-xs text-error"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                <p class="mt-1">{{ $chirp->message }}</p>

                {{-- Likes --}}
                <div class="flex items-center gap-3 mt-4">
                    @auth
                        @if ($userLiked)
                            <form method="POST" action="{{ route('chirps.unlike', $chirp) }}">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-ghost btn-sm text-error"
                                >
                                    ❤️ Curtido
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('chirps.like', $chirp) }}">
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-ghost btn-sm"
                                >
                                    🤍 Curtir
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-sm text-base-content/50">
                            Faça login para curtir
                        </span>
                    @endauth

                    <span class="text-sm text-base-content/60">
                        {{ $likesCount }}
                        {{ $likesCount === 1 ? 'curtida' : 'curtidas' }}
                    </span>
                </div>

                {{-- Formulário de comentários --}}
                @auth
                    <form
                        method="POST"
                        action="{{ route('comments.store', $chirp) }}"
                        class="mt-4"
                    >
                        @csrf

                        <div class="flex gap-2">
                            <input
                                type="text"
                                name="message"
                                placeholder="Escreva um comentário..."
                                class="flex-1 rounded-lg border-gray-300"
                                required
                                maxlength="1000"
                            >

                            <button
                                type="submit"
                                class="rounded-lg bg-gray-800 px-4 py-2 text-white"
                            >
                                Comentar
                            </button>
                        </div>

                        @error('message')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </form>
                @endauth

                {{-- Lista de comentários --}}
                <div class="mt-4 space-y-3">
                    @foreach ($chirp->comments()->with('user')->latest()->get() as $comment)
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="flex items-center justify-between">
                                <strong class="text-sm text-gray-800">
                                    {{ $comment->user?->name ?? 'Usuário removido' }}
                                </strong>

                                <span class="text-xs text-gray-500">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-gray-700">
                                {{ $comment->message }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
