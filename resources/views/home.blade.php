<x-layout>
    <x-slot:title>
        Home Feed
    </x-slot:title>

    <div class="max-w-3xl mx-auto px-4 py-10">

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-sm font-medium text-primary mb-2">
                YOUR COMMUNITY
            </p>

            <h1 class="text-3xl md:text-4xl font-bold tracking-tight">
                What's happening?
            </h1>

            <p class="text-base-content/60 mt-2">
                Share your thoughts with the Chirper community.
            </p>
        </div>

        {{-- Composer --}}
        @auth
            <div class="card bg-base-100 border border-base-300/60 shadow-sm rounded-2xl mb-10">
                <div class="card-body p-5 md:p-6">

                    <a
                        href="{{ route('profile.show', auth()->user()) }}"
                        class="flex items-center gap-3 mb-4 group"
                    >
                    <div class="avatar">
    <div class="rounded-full w-11 h-11">
        @if (auth()->user()->avatar)
            <img
                src="{{ asset('storage/' . auth()->user()->avatar) }}"
                alt="{{ auth()->user()->name }}'s avatar"
                class="rounded-full"
            />
        @else
            <div class="bg-primary text-primary-content rounded-full w-11 h-11 flex items-center justify-center">
                <span class="text-lg">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
        @endif
    </div>
</div>

                        <div>
                            <p class="font-semibold group-hover:text-primary transition-colors">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="text-xs text-base-content/50">
                                Share something with the community
                            </p>
                        </div>
                    </a>

                    <form method="POST" action="/chirps">
                        @csrf

                        <textarea
                            name="message"
                            placeholder="What's on your mind?"
                            class="textarea textarea-bordered w-full resize-none rounded-xl min-h-28 @error('message') textarea-error @enderror"
                            rows="3"
                            maxlength="255"
                            required
                        >{{ old('message') }}</textarea>

                        @error('message')
                            <p class="text-sm text-error mt-2">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-full px-6">
                                Publish Chirp
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card bg-base-100 border border-base-300/60 shadow-sm rounded-2xl mb-10">
                <div class="card-body flex flex-row items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold">Join the conversation!</p>
                        <p class="text-sm text-base-content/60">
                            Sign in to share your thoughts.
                        </p>
                    </div>

                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-full">
                        Sign In
                    </a>
                </div>
            </div>
        @endauth

        {{-- Feed header --}}
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">
                Latest Chirps
            </h2>

            <span class="text-xs text-base-content/50">
                Community feed
            </span>
        </div>

        {{-- Chirps --}}
        <div class="space-y-4">
            @forelse ($chirps as $chirp)
                <x-chirp :chirp="$chirp" />
            @empty
                <div class="text-center py-16 px-6 rounded-2xl border border-dashed border-base-300">
                    <div class="text-4xl mb-4">🐦</div>

                    <h3 class="text-lg font-semibold">
                        No Chirps yet
                    </h3>

                    <p class="text-sm text-base-content/60 mt-2">
                        Be the first to share something with the community!
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-layout>
