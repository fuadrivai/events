<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Event Detail
            </h2>

            <a href="{{ route('dashboard.events') }}" class="text-indigo-600 hover:underline">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <div><b>Title:</b> {{ $event->title }}</div>
                    <div><b>Slug:</b> {{ $event->slug }}</div>
                    <div><b>Status:</b> {{ $event->status }}</div>
                    <a href="{{ route('dashboard.events.fields', $event) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md">
                        Form Builder
                    </a>

                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <b>Next:</b> di sini nanti ada tab/menu: Form Builder, Pricing, Templates, Registrations.
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
