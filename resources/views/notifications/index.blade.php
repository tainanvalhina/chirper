<x-layout>
    <div class="max-w-2xl mx-auto">

        <h1 class="text-2xl font-bold mb-6">
            Minhas notificações
        </h1>

        <div class="space-y-3">

            @forelse ($notifications as $notification)

                <form
                    method="POST"
                    action="{{ route('notifications.read', $notification->id) }}"
                    class="rounded-lg p-4 border border-base-300
                        {{ $notification->read_at
                            ? 'bg-white text-gray-800'
                            : 'bg-blue-100 text-blue-900' }}"
                >
                    @csrf

                    <button type="submit" class="text-left w-full">

                        <strong>
                            {{ $notification->data['user_name'] }}
                        </strong>

                        {{ $notification->data['message'] }}

                        <p class="text-xs mt-2 opacity-70">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>

                    </button>
                </form>

            @empty

                <p class="text-center text-base-content/60 py-8">
                    Você ainda não tem notificações.
                </p>

            @endforelse

        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>

    </div>
</x-layout>