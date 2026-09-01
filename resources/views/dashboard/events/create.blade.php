<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Event - Profesional Network</title>

    <link rel="stylesheet" href="/assets/compiled/css/app.css">
    <link rel="stylesheet" href="/assets/compiled/css/iconly.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Quill Editor CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

    {{-- INLINE STYLE SESUAI TEMPLATE --}}
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f7ff;
        }

        h1,
        h2,
        h3,
        h4,
        .school-title,
        .page-title,
        .form-title {
            font-family: 'Playfair Display', serif;
        }

        /* Header & Logo */
        .header-section {
            background: white;
            padding: 20px 0;
        }

        .school-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }

        .school-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .school-logo {
            max-height: 80px;
            width: auto;
        }

        /* Gold Divider */
        .gold-divider {
            height: 4px;
            background: linear-gradient(90deg, #c5a059 0%, #e6c88b 100%);
            width: 100%;
        }

        /* Form Card Styling */
        .form-section {
            padding: 40px 0;
            min-height: 80vh;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            border-top: 5px solid #c5a059;
        }

        .form-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
        }

        .required {
            color: #dc3545;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #c5a059;
            box-shadow: 0 0 0 0.25rem rgba(197, 160, 89, 0.25);
        }

        /* Buttons */
        .btn-school-primary {
            background-color: #1a1a1a;
            color: #fff;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-school-primary:hover {
            background-color: #333;
            color: #c5a059;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border-radius: 8px;
            padding: 10px 25px;
        }

        /* Quill Fix */
        .ql-toolbar {
            border-radius: 8px 8px 0 0;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .ql-container {
            border-radius: 0 0 8px 8px;
            border-color: #dee2e6;
            font-family: 'Inter', sans-serif;
            min-height: 150px;
        }
    </style>
</head>

<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/assets/images/logo.png" class="school-logo" alt="Logo"
                        onerror="this.style.display='none'">
                </div>
                <div class="col">
                    <h2 class="school-title">
                        Profesional Network
                    </h2>
                    <p class="school-subtitle">
                        Create New Event
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard.events') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="gold-divider"></div>

    <div class="form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    {{-- Alert Success --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0"
                            role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Error Alert --}}
                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm border-0 mb-4" role="alert">
                            <strong><i class="fas fa-exclamation-circle me-2"></i> Terdapat kesalahan:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-card">
                        <h3 class="form-title">Create New Event</h3>

                        <form method="POST" action="{{ route('dashboard.events.store') }}" class="needs-validation"
                            novalidate>
                            @csrf

                            <h4 class="mb-4 text-muted border-bottom pb-2 fs-5">Basic Information</h4>

                            <div class="row">
                                {{-- Title --}}
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Event Title <span class="required">*</span></label>
                                        <input type="text" name="title" class="form-control"
                                            placeholder="Contoh: STEAM Fair 2026" required value="{{ old('title') }}">
                                    </div>
                                </div>

                                {{-- Slug --}}
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Slug / URL <span class="required">*</span></label>
                                        <input type="text" name="slug" id="slug" class="form-control"
                                            readonly placeholder="steam-fair-2026" required value="{{ old('slug') }}">
                                        <small class="text-muted">Slug dibuat otomatis dari judul event.</small>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Status <span class="required">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="DRAFT" selected>Draft (Hidden)</option>
                                            <option value="PUBLISHED">Published (Live)</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Active Until --}}
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Active Until <span class="required"></span></label>
                                        <input type="datetime-local" name="active_until" class="form-control" required
                                            value="{{ old('active_until') }}" placeholder="YYYY-MM-DD HH:mm">
                                        <small class="text-muted">Gunakan format YYYY-MM-DD HH:mm.</small>
                                        <small class="text-muted">Jika tidak diisi, event akan aktif
                                            selama-lamanya.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label">Intro Text (Opsional)</label>
                                <div id="intro-editor">
                                    {!! old('intro_html') !!}
                                </div>
                                {{-- Hidden Input --}}
                                <input type="hidden" name="intro_html" id="intro_html">
                                <div class="form-text mt-2 text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Teks ini tampil di halaman publik sebelum
                                    form pendaftaran.
                                </div>
                            </div>

                            <hr class="my-5 opacity-25">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('dashboard.events') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-school-primary shadow">
                                    <i class="fas fa-save me-2"></i> Save Event
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/extensions/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.querySelector('input[name="title"]');
            const slugInput = document.getElementById('slug');

            function slugify(value) {
                return value
                    .toString()
                    .trim()
                    .toLowerCase()
                    .normalize('NFKD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            const syncSlug = function() {
                const title = titleInput.value;
                slugInput.value = slugify(title);
            };

            titleInput.addEventListener('input', syncSlug);

            if (!slugInput.value && titleInput.value) {
                syncSlug();
            }

            // Init Quill
            const quill = new Quill('#intro-editor', {
                theme: 'snow',
                placeholder: 'Tulis deskripsi atau pengantar event di sini...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{
                            'align': []
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            const introInput = document.getElementById('intro_html');

            // Set initial value jika ada old input (saat validasi error)
            if (introInput.value) {
                quill.root.innerHTML = introInput.value;
            } else {
                introInput.value = quill.root.innerHTML;
            }

            // Sync on change
            quill.on('text-change', function() {
                introInput.value = quill.root.innerHTML;
            });
        });
    </script>
</body>

</html>
