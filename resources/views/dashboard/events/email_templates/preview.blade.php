<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <div class="mb-4">
            <div class="text-sm text-gray-500">Preview Template</div>
            <h1 class="text-xl font-semibold">
                {{ $event->title }} — {{ $key }}
            </h1>
            @if($used_registration_id)
                <div class="text-xs text-gray-500 mt-1">
                    Data contoh dari registration ID: {{ $used_registration_id }}
                </div>
            @endif
        </div>

        <div class="p-4 border rounded-lg bg-white mb-4">
            <div class="text-xs text-gray-500 mb-1">Subject</div>
            <div class="font-semibold">{{ $subject }}</div>
        </div>

        <div class="p-4 border rounded-lg bg-white">
            <div class="text-xs text-gray-500 mb-2">Body (HTML Rendered)</div>
            <div class="prose max-w-none">
                {!! $body !!}
            </div>
        </div>
    </div>
</x-app-layout>
