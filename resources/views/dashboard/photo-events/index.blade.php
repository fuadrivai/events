<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Studio management</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Photo Booth Events</h1>
                <p class="mt-1 text-sm text-slate-500">Atur desain, kamera, dan QR download untuk setiap acara.</p>
            </div>
            <a href="{{ route('dashboard.photo-events.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-amber-700">
                <i class="fas fa-plus"></i>
                Buat photo event
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[#f7f8fa] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if (session('success'))
                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
                    <i class="fas fa-circle-check mt-0.5"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if ($photoEvents->isEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                        <i class="fas fa-camera-retro text-2xl"></i>
                    </div>
                    <h2 class="mt-5 text-xl font-bold text-slate-900">Belum ada photo event</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Buat event pertama, atur tampilan frame, lalu buka booth dari laptop yang tersambung ke kamera.
                    </p>
                    <a href="{{ route('dashboard.photo-events.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <i class="fas fa-plus"></i> Buat photo event pertama
                    </a>
                </section>
            @else
                <div class="mb-5 flex items-center justify-between">
                    <p class="text-sm text-slate-500">
                        {{ $photoEvents->total() }} event{{ $photoEvents->total() === 1 ? '' : 's' }}
                    </p>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-slate-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Penyimpanan foto privat
                    </span>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($photoEvents as $photoEvent)
                        @php
                            $statusClasses = match ($photoEvent->status) {
                                'published' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'closed' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                default => 'bg-slate-100 text-slate-600 ring-slate-200',
                            };
                            $statusLabel = match ($photoEvent->status) {
                                'published' => 'Terbit',
                                'closed' => 'Ditutup',
                                default => 'Draft',
                            };
                        @endphp
                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl">
                            <div class="h-2" style="background: {{ $photoEvent->frame_color }}"></div>
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $photoEvent->name }}</p>
                                        <h2 class="mt-2 truncate font-serif text-2xl font-bold text-slate-900">{{ $photoEvent->title }}</h2>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <p class="mt-3 min-h-10 text-sm leading-5 text-slate-500">
                                    {{ $photoEvent->subtitle ?: $photoEvent->camera_label }}
                                </p>

                                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                                    <span class="text-slate-500"><i class="fas fa-images mr-2 text-slate-400"></i>{{ number_format($photoEvent->photos_count) }} foto di server</span>
                                    <span class="font-mono text-xs text-slate-400">/{{ $photoEvent->slug }}</span>
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <a href="{{ route('dashboard.photo-events.booth', $photoEvent) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                                        <i class="fas fa-camera"></i> Buka booth
                                    </a>
                                    <a href="{{ route('dashboard.photo-events.edit', $photoEvent) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800">
                                        <i class="fas fa-sliders"></i> Pengaturan
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-7">{{ $photoEvents->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
