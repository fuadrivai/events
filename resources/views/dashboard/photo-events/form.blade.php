<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('dashboard.photo-events.index') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Photo Booth
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">
                {{ $photoEvent->exists ? 'Pengaturan photo event' : 'Buat photo event' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">Tentukan identitas acara dan tampilan foto yang akan diterima orang tua.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[#f7f8fa] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <form method="POST"
                action="{{ $photoEvent->exists ? route('dashboard.photo-events.update', $photoEvent) : route('dashboard.photo-events.store') }}"
                class="space-y-6">
                @csrf
                @if ($photoEvent->exists)
                    @method('PUT')
                @endif

                @if (session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                        <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">Periksa kembali data berikut:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <i class="fas fa-sparkles"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">Identitas acara</h2>
                            <p class="mt-1 text-sm text-slate-500">Nama internal untuk admin dan judul yang terlihat di foto.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama event <span class="text-rose-500">*</span></label>
                            <input id="name" name="name" value="{{ old('name', $photoEvent->name) }}" required maxlength="120"
                                placeholder="Contoh: Global Cultural Day 2026"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="slug" class="mb-2 block text-sm font-semibold text-slate-700">Slug <span class="text-rose-500">*</span></label>
                            <div class="flex rounded-xl shadow-sm">
                                <span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-200 bg-slate-50 px-3 text-xs text-slate-500">/booth/</span>
                                <input id="slug" name="slug" value="{{ old('slug', $photoEvent->slug) }}" required maxlength="180"
                                    placeholder="global-cultural-day"
                                    class="min-w-0 flex-1 rounded-r-xl border-slate-200 px-4 py-3 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            @error('slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="active_date" class="mb-2 block text-sm font-semibold text-slate-700">Active date / tanggal acara <span class="text-rose-500">*</span></label>
                            <input id="active_date" name="active_date" type="date"
                                value="{{ old('active_date', optional($photoEvent->active_date)->format('Y-m-d') ?: now()->toDateString()) }}" required
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <p class="mt-1 text-xs text-slate-500">Tanggal ini dicetak pada footer frame; tidak mengubah status event.</p>
                            @error('active_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status event</label>
                            <select id="status" name="status" required
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="draft" @selected(old('status', $photoEvent->status) === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $photoEvent->status) === 'published')>Terbit</option>
                                <option value="closed" @selected(old('status', $photoEvent->status) === 'closed')>Ditutup</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Event ditutup tidak menerima foto baru.</p>
                            @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Judul event pada frame <span class="text-rose-500">*</span></label>
                            <input id="title" name="title" value="{{ old('title', $photoEvent->title) }}" required maxlength="160"
                                placeholder="Contoh: Celebrating Cultures, Together"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="camera_label" class="mb-2 block text-sm font-semibold text-slate-700">Nama sekolah / label frame <span class="text-rose-500">*</span></label>
                            <input id="camera_label" name="camera_label" value="{{ old('camera_label', $photoEvent->camera_label) }}" required maxlength="120"
                                placeholder="Mutiara Harapan Islamic School"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('camera_label')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="subtitle" class="mb-2 block text-sm font-semibold text-slate-700">Teks footer frame <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="subtitle" name="subtitle" value="{{ old('subtitle', $photoEvent->subtitle) }}" maxlength="255"
                                placeholder="Contoh: Celebrating Cultures, Together"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            @error('subtitle')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">Tampilan booth</h2>
                            <p class="mt-1 text-sm text-slate-500">Atur warna, teks, dan durasi hitung mundur. Preview mengikuti hasil foto.</p>
                        </div>
                        <button id="apply-summer-preset" type="button"
                            class="ml-auto inline-flex shrink-0 items-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-900 transition hover:bg-cyan-100">
                            <i class="fas fa-umbrella-beach"></i> Preset Hawaiian
                        </button>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="frame_theme" class="mb-2 block text-sm font-semibold text-slate-700">Gaya frame</label>
                            <select id="frame_theme" name="frame_theme"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="classic" @selected(old('frame_theme', $photoEvent->frame_theme ?: 'classic') === 'classic')>Classic</option>
                                <option value="tropical" @selected(old('frame_theme', $photoEvent->frame_theme ?: 'classic') === 'tropical')>Hawaiian / Tropical</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Ornamen bunga, daun palem, matahari, dan ombak ikut tercetak di frame.</p>
                            @error('frame_theme')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="frame_background_color" class="mb-2 block text-sm font-semibold text-slate-700">Warna dasar frame <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <input id="frame_background_color_picker" type="color" value="{{ old('frame_background_color', $photoEvent->frame_background_color ?: '#54172F') }}"
                                    class="h-12 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                <input id="frame_background_color" name="frame_background_color" value="{{ old('frame_background_color', $photoEvent->frame_background_color ?: '#54172F') }}" required
                                    pattern="^#[0-9A-Fa-f]{6}$" class="w-36 rounded-xl border-slate-200 px-4 py-3 font-mono text-sm uppercase shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <span id="frame-background-swatch" class="h-10 w-10 rounded-xl border border-slate-200 shadow-inner"></span>
                            </div>
                            @error('frame_background_color')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="frame_color" class="mb-2 block text-sm font-semibold text-slate-700">Warna aksen <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-3">
                                <input id="frame_color_picker" type="color" value="{{ old('frame_color', $photoEvent->frame_color ?: '#D9A978') }}"
                                    class="h-12 w-14 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                <input id="frame_color" name="frame_color" value="{{ old('frame_color', $photoEvent->frame_color ?: '#D9A978') }}" required
                                    pattern="^#[0-9A-Fa-f]{6}$" class="w-36 rounded-xl border-slate-200 px-4 py-3 font-mono text-sm uppercase shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                <span id="frame-swatch" class="h-10 w-10 rounded-xl border border-slate-200 shadow-inner"></span>
                            </div>
                            @error('frame_color')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="countdown_seconds" class="mb-2 block text-sm font-semibold text-slate-700">Hitung mundur</label>
                            <select id="countdown_seconds" name="countdown_seconds"
                                class="w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                @foreach ([0, 3, 5, 10] as $seconds)
                                    <option value="{{ $seconds }}" @selected((int) old('countdown_seconds', $photoEvent->countdown_seconds) === $seconds)>
                                        {{ $seconds === 0 ? 'Tanpa hitung mundur' : $seconds . ' detik' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('countdown_seconds')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                    @if ($photoEvent->exists)
                        <a href="{{ route('dashboard.photo-events.booth', $photoEvent) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <i class="fas fa-camera"></i> Buka kamera
                        </a>
                    @else
                        <span></span>
                    @endif
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-600/20 transition hover:bg-amber-700">
                        <i class="fas fa-floppy-disk"></i> Simpan pengaturan
                    </button>
                </div>
            </form>

            <aside class="space-y-5">
                <div class="sticky top-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Preview frame</p>
                        <h2 class="mt-1 font-serif text-lg font-bold text-slate-900">Tampilan foto</h2>
                    </div>
                    <div id="event-preview" class="m-4 overflow-hidden rounded-2xl border-[3px] p-1 shadow-inner"
                        style="border-color: {{ old('frame_color', $photoEvent->frame_color ?: '#D9A978') }}; background-color: {{ old('frame_background_color', $photoEvent->frame_background_color ?: '#54172F') }}; background-image: repeating-linear-gradient(135deg, rgba(255,255,255,.035) 0px, rgba(255,255,255,.035) 1px, transparent 1px, transparent 10px)">
                        <div id="preview-frame-content" class="overflow-hidden rounded-xl p-2"
                            style="background-color: {{ old('frame_background_color', $photoEvent->frame_background_color ?: '#54172F') }}; background-image: repeating-linear-gradient(135deg, rgba(255,255,255,.035) 0px, rgba(255,255,255,.035) 1px, transparent 1px, transparent 10px)">
                            <div id="preview-theme-decoration" class="{{ old('frame_theme', $photoEvent->frame_theme ?: 'classic') === 'tropical' ? 'flex' : 'hidden' }} items-center justify-between px-1 pb-1 text-[10px] font-semibold tracking-wider" aria-hidden="true">
                                <span class="text-amber-300">☀️ &nbsp;〰️</span>
                                <span class="rounded-full border border-white/15 px-2 py-0.5 text-[7px] uppercase text-white/70">Hawaiian</span>
                                <span class="text-emerald-300">🌴 &nbsp;🌺</span>
                            </div>
                            <div class="flex min-h-[58px] items-center gap-2 px-1 pb-2">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-1.5">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="" class="h-full w-full object-contain">
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p id="preview-label" class="truncate text-[7px] font-bold uppercase tracking-[.12em] text-amber-200">{{ old('camera_label', $photoEvent->camera_label) ?: 'Nama sekolah' }}</p>
                                    <p id="preview-title" class="mt-0.5 truncate text-xs font-extrabold uppercase text-white">{{ old('title', $photoEvent->title) ?: 'Judul event' }}</p>
                                </div>
                                <span id="preview-year" class="shrink-0 rounded-full border px-2 py-1 text-[9px] font-bold"
                                    style="border-color: {{ old('frame_color', $photoEvent->frame_color ?: '#D9A978') }}; color: {{ old('frame_color', $photoEvent->frame_color ?: '#D9A978') }}">{{ now()->format('Y') }}</span>
                            </div>
                            <div class="flex aspect-video items-center justify-center overflow-hidden rounded-lg border border-white/10 bg-gradient-to-br from-slate-200 via-slate-100 to-amber-50">
                                <div class="text-center text-slate-400">
                                    <i class="fas fa-camera text-2xl"></i>
                                    <p class="mt-1 text-[9px]">Area foto</p>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2 border-t border-white/10 px-1 pt-2">
                                <p id="preview-subtitle" class="truncate text-[8px] font-extrabold uppercase text-white">{{ old('subtitle', $photoEvent->subtitle) ?: (old('title', $photoEvent->title) ?: 'Teks footer') }}</p>
                                <p id="preview-date" class="shrink-0 text-[8px] font-semibold text-slate-300">{{ now()->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4 text-xs leading-5 text-slate-500">
                        <i class="fas fa-shield-halved mr-1 text-emerald-600"></i>
                        Foto disimpan privat di server. Link download QR berlaku 30 hari.
                    </div>
                </div>
            </aside>
        </div>

        @if ($photoEvent->exists)
            <section class="mx-auto mt-8 max-w-6xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div>
                        <h2 class="font-bold text-slate-900">Foto tersimpan</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $photos->total() }} foto · folder untuk foto baru
                            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">photo-events/{{ $photoEvent->slug }}</code>
                        </p>
                    </div>
                    <a href="{{ route('dashboard.photo-events.booth', $photoEvent) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <i class="fas fa-camera"></i> Buka booth
                    </a>
                </div>

                @if ($photos->isEmpty())
                    <div class="px-6 py-14 text-center sm:px-8">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">
                            <i class="fas fa-images"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold text-slate-700">Belum ada foto di server</p>
                        <p class="mt-1 text-xs text-slate-500">Foto akan muncul di sini setelah berhasil diunggah dari booth.</p>
                    </div>
                @else
                    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-8">
                        @foreach ($photos as $photo)
                            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                @if ($photo->file_available)
                                    <a href="{{ route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]) }}"
                                        target="_blank" rel="noopener" class="group relative block aspect-[4/3] overflow-hidden bg-slate-200">
                                        <img src="{{ route('dashboard.photo-events.photos.preview', [$photoEvent, $photo]) }}"
                                            alt="Foto {{ $photo->created_at->format('d M Y H:i') }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <span class="absolute inset-0 flex items-center justify-center bg-slate-950/0 text-white opacity-0 transition group-hover:bg-slate-950/25 group-hover:opacity-100">
                                            <i class="fas fa-up-right-and-down-left-from-center"></i>
                                        </span>
                                    </a>
                                @else
                                    <div class="flex aspect-[4/3] flex-col items-center justify-center bg-rose-50 text-rose-400">
                                        <i class="fas fa-image-slash text-2xl"></i>
                                        <span class="mt-2 text-xs font-semibold">File tidak ditemukan</span>
                                    </div>
                                @endif
                                <div class="p-3.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-slate-800">{{ $photo->created_at->format('d M Y · H:i') }}</p>
                                            <p class="mt-1 truncate font-mono text-[10px] text-slate-400">{{ $photo->uuid }}</p>
                                        </div>
                                        @if ($photo->expires_at->isFuture())
                                            <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-bold text-emerald-700">QR aktif</span>
                                        @else
                                            <span class="shrink-0 rounded-full bg-slate-200 px-2 py-1 text-[9px] font-bold text-slate-600">QR kedaluwarsa</span>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                        <span class="text-[10px] text-slate-500">{{ $photo->mime_type }}</span>
                                        @if ($photo->file_available && $photo->expires_at->isFuture())
                                            <a href="{{ route('photo.download', $photo->token) }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 hover:text-amber-800">
                                                <i class="fas fa-download"></i> Unduh
                                            </a>
                                        @else
                                            <span class="text-[10px] text-slate-400">{{ $photo->file_available ? 'Link unduh habis' : 'File tidak tersedia' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($photos->hasPages())
                        <div class="border-t border-slate-100 px-6 py-4 sm:px-8">
                            {{ $photos->links() }}
                        </div>
                    @endif
                @endif
            </section>
        @endif
    </div>

    <script>
        (() => {
            const fields = {
                name: document.getElementById('name'),
                slug: document.getElementById('slug'),
                title: document.getElementById('title'),
                label: document.getElementById('camera_label'),
                subtitle: document.getElementById('subtitle'),
                color: document.getElementById('frame_color'),
                colorPicker: document.getElementById('frame_color_picker'),
                swatch: document.getElementById('frame-swatch'),
                backgroundColor: document.getElementById('frame_background_color'),
                backgroundColorPicker: document.getElementById('frame_background_color_picker'),
                backgroundSwatch: document.getElementById('frame-background-swatch'),
                activeDate: document.getElementById('active_date'),
                theme: document.getElementById('frame_theme'),
            };
            const slugIsEditable = {{ $photoEvent->exists ? 'true' : 'false' }};
            let slugWasManuallyChanged = slugIsEditable || fields.slug.value.length > 0;
            const previewSubtitle = document.getElementById('preview-subtitle');
            const updateFrameText = () => {
                const [year, month, day] = fields.activeDate.value.split('-').map(Number);
                const activeDate = year && month && day
                    ? new Date(year, month - 1, day, 12)
                    : new Date();
                document.getElementById('preview-year').textContent = String(activeDate.getFullYear());
                document.getElementById('preview-date').textContent =
                    activeDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                previewSubtitle.textContent =
                    fields.subtitle.value.trim() || fields.title.value.trim() || fields.name.value.trim() || 'Teks footer';
            };

            fields.slug.addEventListener('input', () => slugWasManuallyChanged = true);
            fields.name.addEventListener('input', () => {
                if (!slugWasManuallyChanged) {
                    fields.slug.value = fields.name.value
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-|-$/g, '');
                }
                updateFrameText();
            });
            fields.activeDate.addEventListener('input', updateFrameText);

            const syncColor = (value) => {
                if (!/^#[0-9a-f]{6}$/i.test(value)) return;
                fields.color.value = value.toUpperCase();
                fields.colorPicker.value = value;
                fields.swatch.style.backgroundColor = value;
                document.getElementById('event-preview').style.borderColor = value;
                document.getElementById('preview-year').style.borderColor = value;
                document.getElementById('preview-year').style.color = value;
                document.getElementById('preview-label').style.color = value;
            };
            fields.color.addEventListener('input', () => syncColor(fields.color.value));
            fields.colorPicker.addEventListener('input', () => syncColor(fields.colorPicker.value));
            syncColor(fields.color.value);

            const syncBackgroundColor = (value) => {
                if (!/^#[0-9a-f]{6}$/i.test(value)) return;
                fields.backgroundColor.value = value.toUpperCase();
                fields.backgroundColorPicker.value = value;
                fields.backgroundSwatch.style.backgroundColor = value;
                document.getElementById('event-preview').style.backgroundColor = value;
                document.getElementById('preview-frame-content').style.backgroundColor = value;
            };
            fields.backgroundColor.addEventListener('input', () => syncBackgroundColor(fields.backgroundColor.value));
            fields.backgroundColorPicker.addEventListener('input', () => syncBackgroundColor(fields.backgroundColorPicker.value));
            syncBackgroundColor(fields.backgroundColor.value);
            fields.theme.addEventListener('change', () => {
                document.getElementById('preview-theme-decoration').classList.toggle('hidden', fields.theme.value !== 'tropical');
                document.getElementById('preview-theme-decoration').classList.toggle('flex', fields.theme.value === 'tropical');
            });
            document.getElementById('apply-summer-preset').addEventListener('click', () => {
                fields.theme.value = 'tropical';
                fields.theme.dispatchEvent(new Event('change'));
                syncBackgroundColor('#006B73');
                syncColor('#FFC857');
            });

            fields.title.addEventListener('input', () => {
                document.getElementById('preview-title').textContent = fields.title.value || 'Judul event';
                updateFrameText();
            });
            fields.label.addEventListener('input', () => document.getElementById('preview-label').textContent = fields.label.value || 'Label kamera');
            fields.subtitle.addEventListener('input', updateFrameText);
            updateFrameText();
        })();
    </script>
</x-app-layout>
