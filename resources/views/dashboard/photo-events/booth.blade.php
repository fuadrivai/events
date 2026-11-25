<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $photoEvent->title }} · Photo Booth</title>
    <link rel="icon" type="image/png"
        href="https://mutiaraharapan.sch.id/wp-content/uploads/2024/05/cropped-cropped-hanya-logo-300x300-1-32x32.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        :root { --frame-color: {{ $photoEvent->frame_color }}; }
        * { box-sizing: border-box; }
        html, body { width: 100%; height: 100%; margin: 0; overflow: hidden; background: #10131d; }
        body { font-family: Figtree, sans-serif; }
        .booth-font { font-family: Figtree, sans-serif; }
        .booth-video { transform: scaleX(-1); }
        .booth-glass {
            background: rgba(31, 37, 53, .88);
            border: 1px solid rgba(255, 255, 255, .12);
            box-shadow: 0 12px 32px rgba(11, 15, 25, .24);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .booth-control {
            display: inline-flex;
            width: 46px;
            height: 46px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 9999px;
            background: rgba(35, 40, 56, .88);
            color: #e7e8ef;
            box-shadow: 0 8px 20px rgba(0,0,0,.2);
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }
        .booth-control:hover { transform: translateY(-2px); background: rgba(65, 72, 94, .96); color: white; }
        .booth-control[aria-expanded="true"] { background: var(--frame-color); color: white; }
        .filter-quick {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: .75rem;
            background: rgba(255,255,255,.05);
            padding: .6rem .7rem;
            color: #d7dbea;
            font-size: .7rem;
            font-weight: 700;
            text-align: left;
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }
        .filter-quick:hover { background: rgba(255,255,255,.1); color: white; }
        .filter-quick[aria-pressed="true"] { border-color: var(--frame-color); background: color-mix(in srgb, var(--frame-color) 20%, transparent); color: white; }
        .booth-shutter {
            width: 76px;
            height: 76px;
            border: 4px solid rgba(255,255,255,.95);
            border-radius: 9999px;
            background: linear-gradient(145deg, #ffae36, #f26732);
            box-shadow: 0 0 0 5px rgba(255,255,255,.22), 0 12px 32px rgba(0,0,0,.34);
            color: white;
            transition: transform .15s ease, filter .15s ease;
        }
        .booth-shutter:hover { transform: scale(1.06); filter: brightness(1.08); }
        .booth-shutter:active { transform: scale(.96); }
        .booth-shutter:disabled { cursor: wait; opacity: .7; }
        .booth-theme {
            white-space: nowrap;
            border: 1px solid transparent;
            border-radius: 9999px;
            color: #aeb5c7;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }
        .booth-theme:hover { color: white; background: rgba(255,255,255,.08); }
        .booth-theme[aria-pressed="true"] {
            border-color: rgba(255,255,255,.15);
            background: linear-gradient(135deg, #f2a31b, #e68112);
            color: white;
            box-shadow: 0 4px 14px rgba(212, 125, 12, .32);
        }
        .booth-scrollbar::-webkit-scrollbar { width: 6px; }
        .booth-scrollbar::-webkit-scrollbar-thumb { border-radius: 99px; background: #50576a; }
        @keyframes booth-confetti-fall {
            to { transform: translate3d(var(--confetti-drift), 110vh, 0) rotate(760deg); opacity: 0; }
        }
        .booth-confetti-piece {
            position: absolute;
            top: -20px;
            width: 9px;
            height: 15px;
            border-radius: 2px;
            animation: booth-confetti-fall 2.2s cubic-bezier(.2,.7,.3,1) forwards;
        }
        @keyframes booth-processing-spin { to { transform: rotate(360deg); } }
        @keyframes booth-processing-glow {
            50% { transform: scale(1.12); opacity: .55; }
        }
        .booth-processing-spin { animation: booth-processing-spin 1.8s linear infinite; }
        .booth-processing-glow { animation: booth-processing-glow 2.4s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) {
            .booth-processing-spin, .booth-processing-glow { animation: none; }
        }
        @media (max-width: 640px) {
            .booth-control { width: 40px; height: 40px; }
            .booth-shutter { width: 66px; height: 66px; }
        }
    </style>
</head>

<body>
    <main id="photo-booth"
        class="booth-font fixed inset-0 z-50 overflow-hidden bg-slate-950 text-white"
        data-event-id="{{ $photoEvent->id }}"
        data-event-slug="{{ $photoEvent->slug }}"
        data-event-name="{{ $photoEvent->name }}"
        data-event-status="{{ $photoEvent->status }}"
        data-active-date="{{ optional($photoEvent->active_date)->format('Y-m-d') }}"
        data-upload-url="{{ route('dashboard.photo-events.photos.store', $photoEvent) }}"
        data-csrf="{{ csrf_token() }}"
        data-title="{{ $photoEvent->title }}"
        data-label="{{ $photoEvent->camera_label }}"
        data-subtitle="{{ $photoEvent->subtitle }}"
        data-frame-color="{{ $photoEvent->frame_color }}"
        data-frame-background-color="{{ $photoEvent->frame_background_color ?: '#54172F' }}"
        data-frame-theme="{{ $photoEvent->frame_theme ?: 'classic' }}"
        data-frame-mode="{{ $photoEvent->frame_mode ?: 'classic' }}"
        data-template-url="{{ $photoEvent->frame_mode === 'template' && $photoEvent->template_path ? route('dashboard.photo-events.template', $photoEvent) : '' }}"
        data-frame-layout="{{ json_encode($photoEvent->frame_layout ?: \App\Models\PhotoEvent::defaultFrameLayout()) }}"
        data-mediapipe-assets="{{ asset('vendor/mediapipe') }}"
        data-countdown="{{ $photoEvent->countdown_seconds }}">
        <div class="absolute inset-0 bg-slate-950">
            <video id="camera-video" class="booth-video absolute inset-0 h-full w-full object-cover" autoplay muted playsinline></video>
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/25 via-transparent to-slate-950/30"></div>
            <canvas id="ar-canvas" class="booth-video booth-ar-layer pointer-events-none absolute inset-0 z-[3] h-full w-full object-cover"></canvas>

            <div id="camera-placeholder" class="absolute inset-0 z-10 flex items-center justify-center bg-[#171b27] text-center">
                <div class="px-6">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border border-white/10 bg-white/5 text-3xl text-amber-300">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h2 class="mt-5 font-serif text-2xl font-bold text-white">{{ $photoEvent->title }}</h2>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-300">
                        Sambungkan kamera SLR atau webcam untuk memulai sesi foto.
                    </p>
                    <button id="start-camera-empty" type="button"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-950/30 transition hover:bg-amber-400">
                        <i class="fas fa-video"></i> Aktifkan kamera
                    </button>
                    <p class="mt-4 text-xs leading-5 text-slate-400">Izinkan akses kamera saat browser meminta izin. Untuk uji lokal, buka aplikasi lewat <strong class="text-slate-300">http://localhost</strong> (localhost mendukung kamera tanpa HTTPS); domain publik tetap perlu HTTPS.</p>
                </div>
            </div>

        </div>

        <header class="pointer-events-none absolute inset-x-0 top-0 z-20 flex items-start justify-between gap-3 p-4 sm:p-6">
            <div class="pointer-events-auto flex min-w-0 items-center gap-3 rounded-full bg-slate-800/90 py-2 pl-3 pr-5 shadow-xl ring-1 ring-white/10 backdrop-blur-md sm:py-2.5 sm:pl-4 sm:pr-7">
                <a href="{{ route('dashboard.photo-events.index') }}"
                    class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white p-1 shadow-inner sm:h-10 sm:w-10"
                    aria-label="Kembali ke daftar photo event">
                    <img id="booth-frame-logo" src="{{ asset('assets/images/logo.png') }}" alt="Logo Mutiara Harapan Islamic School"
                        class="h-full w-full object-contain">
                </a>
                <div class="min-w-0">
                    <p class="truncate text-xs font-extrabold uppercase tracking-[.12em] text-white sm:text-sm">{{ $photoEvent->title }}</p>
                    <p class="mt-0.5 truncate text-[9px] font-semibold uppercase tracking-[.12em] text-cyan-300 sm:text-[10px]">{{ $photoEvent->camera_label }}</p>
                </div>
            </div>

            <div id="camera-status" class="booth-glass pointer-events-none absolute left-1/2 top-5 hidden -translate-x-1/2 items-center gap-2 rounded-full px-4 py-2 text-[11px] font-semibold text-slate-100 sm:top-7 sm:flex">
                <span id="camera-status-dot" class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,.8)]"></span>
                <span id="camera-status-text">Kamera siap</span>
            </div>
            <div id="face-status" class="booth-glass pointer-events-none absolute left-4 top-[76px] z-20 rounded-full px-3 py-1.5 text-[10px] font-semibold text-slate-200 sm:left-6 sm:top-24">
                AR · Menyiapkan pelacakan wajah
            </div>

            <div class="pointer-events-auto flex shrink-0 items-center gap-2 sm:gap-3">
                <button id="open-stickers" type="button" class="booth-control" aria-label="Filter AR dan stiker" aria-expanded="false" title="Filter AR dan stiker">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </button>
                <button id="open-settings" type="button" class="booth-control" aria-label="Pengaturan kamera" aria-expanded="false" title="Pengaturan kamera">
                    <i class="fas fa-gear"></i>
                </button>
                <button id="toggle-fullscreen" type="button" class="booth-control" aria-label="Layar penuh" title="Layar penuh">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </header>

        <div id="booth-message" role="status" aria-live="polite"
            class="absolute left-1/2 top-24 z-40 hidden w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 rounded-2xl border px-4 py-3 text-sm shadow-xl backdrop-blur-md"></div>

        <div id="sticker-panel" class="booth-glass booth-scrollbar absolute right-4 top-[76px] z-30 hidden max-h-[min(76vh,680px)] w-[min(360px,calc(100vw-2rem))] overflow-y-auto rounded-2xl p-4 sm:right-6 sm:top-24">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-white">Filter AR & stiker</p>
                    <p class="mt-1 text-[11px] text-slate-300">Aktifkan efek yang mengikuti wajah.</p>
                </div>
                <button type="button" data-close-panel="sticker-panel" class="text-slate-300 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" class="filter-quick" data-ar-toggle="headband" aria-pressed="false"><span>👑</span> Headband</button>
                <button type="button" class="filter-quick" data-ar-toggle="cheekSticker" aria-pressed="true"><span>🏳️</span> Bendera pipi</button>
                <button type="button" class="filter-quick" data-ar-toggle="glasses" aria-pressed="false"><span>🕶️</span> Kacamata</button>
                <button type="button" class="filter-quick" data-ar-cycle="glassesStyle"><span>🎨</span> Kacamata: <span data-cycle-label="glassesStyle">Classic</span></button>
                <button type="button" class="filter-quick" data-ar-toggle="cap" aria-pressed="false"><span>🧢</span> Topi</button>
                <button type="button" class="filter-quick" data-ar-toggle="outfit" aria-pressed="false"><span>👘</span> Kostum tema</button>
            </div>
        </div>

        <div id="settings-panel" class="booth-glass booth-scrollbar absolute right-4 top-[76px] z-30 hidden max-h-[min(82vh,760px)] w-[min(420px,calc(100vw-2rem))] overflow-y-auto rounded-2xl p-4 sm:right-6 sm:top-24">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-white">AR Filter & Pengaturan Kamera</p>
                    <p class="mt-1 text-[11px] text-slate-300">Pengaturan berlaku selama sesi booth ini.</p>
                </div>
                <button type="button" data-close-panel="settings-panel" class="text-slate-300 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <div class="mt-3 divide-y divide-white/10">
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Durasi hitung mundur</span><span class="block text-[10px] text-slate-400">Persiapan sebelum foto diambil</span></span>
                    <select id="select-countdown" class="rounded-lg border-white/10 bg-slate-900/80 px-2.5 py-2 text-xs font-semibold text-white">
                        @foreach ([3, 5, 10] as $seconds)
                            <option value="{{ $seconds }}" @selected((int) $photoEvent->countdown_seconds === $seconds)>{{ $seconds }} detik</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Format unduhan utama</span><span class="block text-[10px] text-slate-400">Tombol unduh lokal</span></span>
                    <select id="select-default-format" class="rounded-lg border-white/10 bg-slate-900/80 px-2.5 py-2 text-xs font-semibold text-white">
                        <option value="jpg">JPG</option>
                        <option value="png">PNG</option>
                    </select>
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Sensitivitas pelacakan wajah</span><span class="block text-[10px] text-slate-400">High membantu wajah kecil/jauh</span></span>
                    <select id="select-face-sensitivity" class="max-w-40 rounded-lg border-white/10 bg-slate-900/80 px-2.5 py-2 text-xs font-semibold text-white">
                        <option value="balanced">Balanced</option>
                        <option value="high">High · wajah jauh</option>
                        <option value="strict">Strict · stabil</option>
                    </select>
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Bandana / headband</span><span class="block text-[10px] text-slate-400">Mengikuti kontur dahi</span></span>
                    <input id="check-headband" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Kacamata AR</span><span class="block text-[10px] text-slate-400">Kacamata mengikuti posisi mata</span></span>
                    <input id="check-glasses" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Gaya kacamata</span><span class="block text-[10px] text-slate-400">Pilih bentuk bingkai</span></span>
                    <select id="select-glasses-style" class="max-w-40 rounded-lg border-white/10 bg-slate-900/80 px-2.5 py-2 text-xs font-semibold text-white">
                        <option value="classic">Classic</option>
                        <option value="round">Round</option>
                        <option value="aviator">Aviator</option>
                        <option value="heart">Heart</option>
                    </select>
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Kostum tema</span><span class="block text-[10px] text-slate-400">Aksen pakaian di bawah wajah</span></span>
                    <input id="check-outfit" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Topi festival</span><span class="block text-[10px] text-slate-400">Stiker topi mengikuti kepala</span></span>
                    <input id="check-cap" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Bendera pipi</span><span class="block text-[10px] text-slate-400">Bendera negara sesuai wilayah pilihan</span></span>
                    <input id="check-face-sticker" type="checkbox" checked class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Posisi stiker pipi</span><span class="block text-[10px] text-slate-400">Pilih sisi wajah</span></span>
                    <select id="select-sticker-side" class="rounded-lg border-white/10 bg-slate-900/80 px-2.5 py-2 text-xs font-semibold text-white">
                        <option value="right">Pipi kanan</option>
                        <option value="left">Pipi kiri</option>
                    </select>
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Nama negara</span><span class="block text-[10px] text-slate-400">Badge mengikuti wajah</span></span>
                    <input id="check-face-label" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Mirror preview kamera</span><span class="block text-[10px] text-slate-400">Foto hasil tetap orientasi normal</span></span>
                    <input id="check-mirror-camera" type="checkbox" checked class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
                <label class="flex items-center justify-between gap-3 py-3">
                    <span><span class="block text-xs font-semibold text-white">Efek suara</span><span class="block text-[10px] text-slate-400">Bunyi countdown dan shutter</span></span>
                    <input id="check-sound-effects" type="checkbox" checked class="h-4 w-4 rounded border-white/20 bg-slate-800 text-amber-500 focus:ring-amber-400">
                </label>
            </div>
            <label for="camera-select" class="mb-2 mt-4 block text-xs font-semibold text-slate-200">Perangkat kamera</label>
            <select id="camera-select" class="w-full rounded-xl border-white/10 bg-slate-900/80 px-3 py-2.5 text-sm text-white focus:border-amber-400 focus:ring-amber-400">
                <option value="">Pilih kamera</option>
            </select>
            <button id="start-camera" type="button"
                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15">
                <i class="fas fa-video"></i> Hubungkan kamera
            </button>
            <div class="mt-4 flex items-center justify-between rounded-xl bg-black/20 px-3 py-2.5">
                <div>
                    <p class="text-xs font-semibold text-white">Koneksi server</p>
                    <p id="connection-status" class="mt-0.5 text-[11px] text-emerald-300">Online</p>
                </div>
                <span id="queue-count" class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-slate-200">0 menunggu</span>
            </div>
            <p id="storage-persistence" class="mt-3 text-[10px] leading-4 text-slate-300">Memeriksa perlindungan penyimpanan browser…</p>
            <div class="mt-4 flex gap-2">
                <button id="retry-uploads" type="button"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-white/10 px-3 py-2.5 text-xs font-semibold text-slate-100 transition hover:bg-white/10">
                    <i class="fas fa-rotate"></i> Coba upload
                </button>
                <a href="{{ route('dashboard.photo-events.edit', $photoEvent) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-amber-400">
                    <i class="fas fa-sliders"></i> Event
                </a>
            </div>
        </div>

        <div id="gallery-drawer" class="booth-glass booth-scrollbar absolute bottom-28 left-4 z-30 hidden max-h-[55vh] w-[min(420px,calc(100vw-2rem))] overflow-y-auto rounded-2xl p-4 sm:bottom-32 sm:left-6">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-white">Foto event ini</p>
                    <p class="mt-1 text-[11px] text-slate-300">Foto lokal dan status QR download.</p>
                </div>
                <button type="button" data-close-panel="gallery-drawer" class="text-slate-300 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <div id="photo-gallery" class="space-y-3">
                <div id="gallery-empty" class="py-7 text-center text-xs text-slate-300">Belum ada foto tersimpan di sesi event ini.</div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 flex flex-col items-center pb-[max(1.1rem,env(safe-area-inset-bottom))]">
            <div class="pointer-events-auto mb-4 flex items-center justify-center gap-5">
                <button id="open-gallery" type="button"
                    class="booth-control !h-11 !w-11" aria-label="Lihat foto tersimpan" title="Lihat foto tersimpan">
                    <i class="fas fa-images"></i>
                </button>
                <button id="capture-photo" type="button" @disabled($photoEvent->status === 'closed')
                    class="booth-shutter inline-flex items-center justify-center text-[27px] disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Ambil foto" title="Ambil foto">
                    <i class="fas fa-camera"></i>
                </button>
                <button id="open-latest" type="button"
                    class="booth-control !h-11 !w-11" aria-label="Lihat foto terakhir" title="Lihat foto terakhir">
                    <i class="fas fa-qrcode"></i>
                </button>
            </div>

            <nav aria-label="Pilih tema foto" class="pointer-events-auto booth-glass max-w-[calc(100vw-1.25rem)] overflow-x-auto rounded-full p-1.5 sm:max-w-[min(94vw,1040px)] sm:px-2">
                <div class="flex min-w-max items-center gap-1">
                    @foreach ([
                        ['🌍', 'All Continents'],
                        ['🏮', 'Asia'],
                        ['🏰', 'Europe'],
                        ['🏜️', 'North Africa'],
                        ['🥁', 'West Africa'],
                        ['🦒', 'Southern Africa'],
                        ['🌊', 'Oceania'],
                    ] as [$icon, $region])
                        <button type="button" class="booth-theme px-3 py-2 text-[10px] font-bold sm:px-4 sm:text-xs"
                                data-region="{{ $region }}"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="mr-1.5">{{ $icon }}</span>{{ $region }}
                        </button>
                    @endforeach
                </div>
            </nav>
        </div>

        <div id="countdown-overlay" class="pointer-events-none absolute inset-0 z-40 hidden items-center justify-center bg-slate-950/30 backdrop-blur-[2px]">
            <span id="countdown-number" class="text-8xl font-extrabold text-white drop-shadow-2xl sm:text-9xl"></span>
        </div>

        <div id="capture-processing" class="fixed inset-0 z-[80] hidden items-center justify-center overflow-hidden bg-[#090d16]/95 p-5 backdrop-blur-xl"
            role="status" aria-live="polite" aria-hidden="true">
            <div class="booth-processing-glow pointer-events-none absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-400/15 blur-3xl sm:h-96 sm:w-96"></div>
            <section class="relative w-full max-w-md overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-b from-slate-800/95 to-slate-900/95 p-7 text-center shadow-[0_30px_100px_rgba(0,0,0,.55)] sm:p-10">
                <div class="absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-amber-300/70 to-transparent"></div>
                <div class="relative mx-auto flex h-28 w-28 items-center justify-center">
                    <span class="booth-processing-spin absolute inset-0 rounded-full border border-amber-300/15 border-t-amber-300/90"></span>
                    <span class="absolute inset-2 rounded-full border border-white/10 border-b-cyan-300/70"></span>
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-300 to-orange-500 text-2xl text-white shadow-[0_0_40px_rgba(245,158,11,.3)]">
                        <i class="fas fa-camera-retro"></i>
                    </span>
                    <span class="absolute right-1 top-4 h-2.5 w-2.5 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,.9)]"></span>
                </div>
                <p class="mt-7 text-[10px] font-bold uppercase tracking-[.28em] text-amber-300">Momen spesial Anda</p>
                <h2 id="capture-processing-title" class="mt-3 font-serif text-2xl font-bold text-white sm:text-3xl">Sedang menyiapkan foto</h2>
                <p id="capture-processing-message" class="mx-auto mt-3 max-w-xs text-sm leading-6 text-slate-300">
                    Tunggu sebentar, kami sedang merapikan hasil foto Anda.
                </p>
                <div class="mx-auto mt-7 flex max-w-[220px] items-center justify-center gap-2" aria-hidden="true">
                    <span class="h-1.5 flex-1 rounded-full bg-gradient-to-r from-amber-300 to-orange-400"></span>
                    <span class="h-1.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 to-cyan-300 opacity-70"></span>
                    <span class="h-1.5 flex-1 rounded-full bg-white/15"></span>
                </div>
                <p class="mt-3 text-[10px] font-medium tracking-wide text-slate-500">Preview & QR code akan segera siap</p>
            </section>
        </div>

        <div id="capture-result" class="fixed inset-0 z-[60] hidden items-center justify-center overflow-y-auto bg-[#090d16]/95 p-3 backdrop-blur-md sm:p-6">
            <section class="relative my-auto w-full max-w-6xl overflow-hidden rounded-3xl border border-white/10 bg-[#111827] shadow-2xl">
                <div class="grid md:grid-cols-[minmax(0,1.1fr)_minmax(340px,.9fr)]">
                    <div class="flex min-h-[280px] items-center justify-center bg-[#080b12] p-4 sm:p-7">
                        <img id="latest-image" alt="Hasil foto event" class="max-h-[72vh] w-full rounded-xl object-contain shadow-2xl">
                    </div>
                    <div class="flex flex-col justify-center border-t border-white/5 p-5 sm:p-8 md:border-l md:border-t-0">
                        <div class="mb-5">
                            <h2 class="font-serif text-2xl font-bold text-white sm:text-3xl">Awesome Photo! <span class="text-amber-300">🎉</span></h2>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Momen spesial {{ $photoEvent->name }} bersama keluarga dan teman.
                            </p>
                        </div>

                        <div id="upload-status-card" class="rounded-2xl border border-white/10 bg-slate-900/80 p-4">
                            <div class="flex items-start gap-3">
                                <span id="upload-status-icon" class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-sm text-amber-300">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p id="upload-status-heading" class="text-xs font-bold text-white">Menyiapkan foto...</p>
                                        <span id="latest-status" class="rounded-full bg-white/10 px-2.5 py-1 text-[9px] font-semibold text-slate-300">Menunggu</span>
                                    </div>
                                    <p id="latest-upload-message" class="mt-1 text-[11px] leading-5 text-slate-400"></p>
                                    <div id="upload-progress-track" class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-700">
                                        <div id="upload-progress-bar" class="h-full w-1/3 rounded-full bg-gradient-to-r from-cyan-400 to-emerald-400 transition-all duration-500"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="upload-error-panel" class="mt-3 hidden rounded-2xl border border-rose-400/25 bg-rose-950/30 p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-exclamation mt-0.5 text-rose-300"></i>
                                <div>
                                    <p class="text-xs font-bold text-rose-200">Foto belum tersimpan di server</p>
                                    <p id="upload-error-message" class="mt-1 text-[11px] leading-5 text-rose-100/80"></p>
                                    <p class="mt-2 text-[10px] leading-4 text-slate-300">Foto tetap aman tersimpan di browser laptop. Anda bisa mengunduhnya sekarang; upload akan dicoba lagi saat koneksi pulih.</p>
                                </div>
                            </div>
                        </div>

                        <div id="latest-qr-panel" class="mt-3 hidden rounded-2xl border border-emerald-400/20 bg-emerald-950/20 p-4 text-center">
                            <p class="text-xs font-bold text-emerald-200">QR download siap dipindai</p>
                            <canvas id="latest-qr" class="mx-auto mt-3 rounded-xl bg-white p-2"></canvas>
                            <p class="mt-2 text-[10px] text-emerald-100/80">Scan dengan kamera ponsel untuk mengunduh foto.</p>
                            <p id="latest-expiry" class="mt-1 text-[9px] text-slate-400"></p>
                            <a id="open-download" href="#" target="_blank" rel="noopener"
                                class="mt-3 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-emerald-600">
                                <i class="fas fa-arrow-up-right-from-square"></i> Buka link download
                            </a>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <button id="download-latest-jpg" type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 px-3 py-3 text-xs font-bold text-white shadow-lg shadow-amber-950/20 transition hover:brightness-110">
                                <i class="fas fa-floppy-disk"></i> Download JPG
                            </button>
                            <button id="download-latest-png" type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-sky-500 to-blue-700 px-3 py-3 text-xs font-bold text-white shadow-lg shadow-blue-950/20 transition hover:brightness-110">
                                <i class="fas fa-image"></i> Download PNG
                            </button>
                        </div>
                        <button id="take-another" type="button"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                            <i class="fas fa-camera-rotate"></i> Take Another (Back to Camera)
                        </button>
                    </div>
                </div>
            </section>
        </div>
        <div id="result-confetti" aria-hidden="true" class="pointer-events-none fixed inset-0 z-[70] hidden overflow-hidden"></div>

        @if ($photoEvent->status === 'closed')
            <div class="absolute left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-rose-300/30 bg-rose-950/90 p-5 text-center text-white shadow-2xl">
                <i class="fas fa-circle-exclamation text-2xl text-rose-300"></i>
                <p class="mt-3 font-semibold">Photo event ini sudah ditutup.</p>
                <a href="{{ route('dashboard.photo-events.edit', $photoEvent) }}" class="mt-4 inline-flex rounded-xl bg-white px-4 py-2 text-sm font-bold text-rose-900">Buka pengaturan</a>
            </div>
        @endif
    </main>

    <script src="{{ asset('vendor/mediapipe/face_mesh.js') }}"></script>
    @vite('resources/js/photo-booth.js')
</body>

</html>
