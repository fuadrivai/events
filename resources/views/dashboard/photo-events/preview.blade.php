<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Preview foto · {{ $photoEvent->title }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; height: 100%; margin: 0; overflow: hidden; background: #050609; }
        .preview-stage { position: fixed; inset: 0; display: grid; place-items: center; }
        .preview-image {
            width: auto;
            height: auto;
            max-width: min(calc(100vw - 112px), 1400px);
            max-height: 90vh;
            object-fit: contain;
        }
        .preview-navigation { position: fixed; inset: 0; z-index: 10; pointer-events: none; }
        .preview-arrow {
            position: fixed;
            z-index: 11;
            top: 50%;
            display: grid;
            width: 60px;
            height: 60px;
            place-items: center;
            transform: translateY(-50%);
            pointer-events: auto;
            border: 1px solid rgba(255,255,255,.45);
            border-radius: 50%;
            background: rgba(15,23,42,.82);
            color: white;
            box-shadow: 0 8px 28px rgba(0,0,0,.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: background .18s ease, transform .18s ease;
        }
        .preview-arrow:hover { transform: translateY(-50%) scale(1.06); background: rgba(245,158,11,.9); }
        .preview-arrow:focus-visible { outline: 3px solid #fbbf24; outline-offset: 3px; }
        .preview-arrow:disabled { cursor: default; opacity: .3; }
        .preview-arrow:disabled:hover { transform: translateY(-50%); background: rgba(15,23,42,.58); }
        .preview-arrow svg { width: 23px; height: 23px; }
        .preview-arrow--left { left: clamp(20px, 3vw, 48px); }
        .preview-arrow--right { right: clamp(20px, 3vw, 48px); }
        @media (max-width: 640px) {
            .preview-arrow { width: 50px; height: 50px; }
            .preview-arrow svg { width: 20px; height: 20px; }
        }
    </style>
</head>
<body>
    <main class="preview-stage">
        <img class="preview-image"
            src="{{ route('dashboard.photo-events.photos.image', [$photoEvent, $photo]) }}"
            alt="Foto {{ $photo->created_at->format('d M Y H:i') }}">

        <nav class="preview-navigation" aria-label="Navigasi preview foto">
            @if ($previousPhoto)
                <a class="preview-arrow preview-arrow--left"
                    href="{{ route('dashboard.photo-events.photos.preview', [$photoEvent, $previousPhoto]) }}"
                    aria-label="Foto sebelumnya" title="Foto sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>
            @else
                <button class="preview-arrow preview-arrow--left" type="button" aria-label="Foto sebelumnya" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </button>
            @endif

            @if ($nextPhoto)
                <a class="preview-arrow preview-arrow--right"
                    href="{{ route('dashboard.photo-events.photos.preview', [$photoEvent, $nextPhoto]) }}"
                    aria-label="Foto selanjutnya" title="Foto selanjutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            @else
                <button class="preview-arrow preview-arrow--right" type="button" aria-label="Foto selanjutnya" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>
            @endif
        </nav>
    </main>
</body>
</html>
