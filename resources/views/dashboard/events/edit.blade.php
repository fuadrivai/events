<x-app-layout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $event->title }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <link rel="stylesheet" href="/assets/compiled/css/app.css">
        <link rel="stylesheet" href="/assets/compiled/css/iconly.css">

        <link
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&amp;family=Inter:wght@300;400;500;600&amp;display=swap"
            rel="stylesheet" />

        <link rel="stylesheet" href="/assets/extensions/select2/dist/css/select2.min.css">
        <link rel="stylesheet" href="/assets/extensions/bootstrap-datepicker/css/bootstrap-datepicker.css">
        <link rel="stylesheet" href="/assets/extensions/toastify-js/src/toastify.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

        {{-- Quill Editor CSS (Diperlukan untuk editor intro) --}}
        <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

        <link rel="stylesheet" href="/assets/static/css/school-visit.css?v=1.1.3">

        {{-- 
       INLINE STYLE: 
       Saya menyertakan style ini agar tampilan "Gold Divider", "Font Playfair", dan "Form Card" 
       langsung terbentuk persis referensi meskipun Anda belum membuat file 'school-visit.css'.
    --}}
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

            /* Gold Divider (Penting) */
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
            .btn-success {
                background-color: #198754;
                border-color: #198754;
                color: white;
                border-radius: 8px;
                padding: 10px 30px;
                font-weight: 600;
            }

            .btn-success:hover {
                background-color: #157347;
            }

            .btn-outline-secondary {
                border-radius: 8px;
                padding: 10px 25px;
            }

            /* Quill Editor Fix */
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
                        {{-- Logo User --}}
                        <img src="/assets/images/logo.png" width="100%" height="100%" class="img school-logo"
                            alt="Logo" onerror="this.style.display='none'">
                    </div>
                    <div class="col">
                        <h2 class="school-title" id="school-name">
                            Mutiara Harapan Islamic School
                        </h2>
                        <p class="school-subtitle" id="school-levels">
                            {{ $event->title }}
                        </p>
                    </div>
                    {{-- Tombol Back to Detail (Optional, ditaruh di kanan header) --}}
                    <div class="col-auto">
                        <a href="{{ route('dashboard.events') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="gold-divider"></div>

        <div class="" style="padding-top: 25px;" id="form-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">

                        {{-- Error Alerts --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <strong><i class="fas fa-exclamation-circle me-2"></i> Terdapat kesalahan:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="form-card">

                            {{-- Form Mulai --}}
                            <form id="edit-event-form" method="POST"
                                action="{{ route('dashboard.events.update', $event) }}" class="needs-validation"
                                novalidate>
                                @csrf
                                @method('PUT')

                                <h4 class="mb-4">Event Details</h4>

                                {{-- Row 1: Title & Slug --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="title" class="form-label">Event Title <span
                                                    class="required">*</span></label>
                                            <input type="text" class="form-control" name="title" id="title"
                                                value="{{ old('title', $event->title) }}" required
                                                placeholder="Contoh: Open Day 2026" />
                                            <div class="invalid-feedback">
                                                Please provide an event title.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="slug" class="form-label">Slug / URL <span
                                                    class="required">*</span></label>
                                            <input type="text" class="form-control" name="slug" id="slug"
                                                value="{{ old('slug', $event->slug) }}" required
                                                placeholder="open-day-2026" />
                                            <div class="invalid-feedback">
                                                Please provide a valid slug.
                                            </div>
                                            <small class="text-muted">URL: /e/{{ $event->slug }}</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Row 2: Status & Price Label --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="status" class="form-label">Status <span
                                                    class="required">*</span></label>
                                            @can('updateStatus', $event)
                                                <select style="width: 100%" name="status" class="form-select"
                                                    id="status" required>
                                                    @php $statusVal = old('status', $event->status); @endphp
                                                    <option value="DRAFT" @selected($statusVal === 'DRAFT')>Draft (Hidden)
                                                    </option>
                                                    <option value="PUBLISHED" @selected($statusVal === 'PUBLISHED')>Published (Live)
                                                    </option>
                                                    <option value="CLOSED" @selected($statusVal === 'CLOSED')>Closed (Selesai)
                                                    </option>
                                                </select>
                                            @else
                                                <select style="width: 100%" name="status" class="form-select"
                                                    id="status" required>
                                                    @php $statusVal = old('status', $event->status); @endphp
                                                    <option value="DRAFT" @selected($statusVal === 'DRAFT')>Draft (Hidden)
                                                    </option>
                                                </select>
                                            @endcan
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="price_label" class="form-label">Label Harga (Opsional)</label>
                                            <input type="text" class="form-control" name="price_question_label"
                                                id="price_label"
                                                value="{{ old('price_question_label', $event->price_question_label) }}"
                                                placeholder="Contoh: Kategori Peserta" />

                                            <div class="mt-2">
                                                <a href="{{ route('prices.index', ['orgSlug' => $event->slug, 'eventId' => $event->id]) }}"
                                                    class="btn btn-sm btn-outline-dark">
                                                    <i class="fas fa-tags me-1"></i> Manage Pricing
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="active_until" class="form-label">Active Until<span
                                                    class="required">*</span></label>
                                            <input type="datetime-local" name="active_until" class="form-control"
                                                required value="{{ old('active_until', $event->active_until) }}"
                                                placeholder="YYYY-MM-DD HH:mm">
                                            <small class="text-muted">Gunakan format YYYY-MM-DD HH:mm.</small>
                                            <div class="invalid-feedback">
                                                Please provide a valid Date.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h4 class="mb-4">Description</h4>

                                {{-- Row 3: Intro Editor --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Intro Text</label>

                                            <div id="intro-editor">
                                                {!! old('intro_html', $event->intro_html) !!}
                                            </div>

                                            <input type="hidden" name="intro_html" id="intro_html">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                {{-- Action Buttons --}}
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <a href="{{ route('public.event.show', $event->slug) }}" target="_blank"
                                            class="btn btn-outline-secondary" id="preview-btn">
                                            <i class="fas fa-external-link-alt me-2"></i> Preview
                                        </a>
                                        <a href="{{ route('dashboard.events') }}"
                                            class="btn btn-link text-muted text-decoration-none ms-2">
                                            Cancel
                                        </a>
                                    </div>
                                    <a href="{{ route('dashboard.events.fields', $event) }}"
                                        class="btn btn-outline-primary">
                                        <i class="fas fa-plus-circle me-2"></i> Manage Questions
                                    </a>
                                    <a href="{{ route('admin.events.email-templates.index', ['orgSlug' => $event->slug, 'eventId' => $event->id]) }}"
                                        class="btn btn-outline-primary">
                                        <i class="fas fa-envelope me-2"></i> Manage Email Templates
                                    </a>
                                    <button type="submit" class="btn btn-success submit-btn" id="submit-btn">
                                        <span class="submit-text"><i class="fas fa-save me-2"></i> Save Changes</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" id="success-message"
                style="position: fixed; top: 20px; right: 20px; z-index: 1050; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <i class="fas fa-check-circle me-2"></i> <strong>Success!</strong> {{ session('success') }}
            </div>
            <script>
                // Auto hide success message
                setTimeout(function() {
                    document.getElementById('success-message').classList.add('d-none');
                }, 5000);
            </script>
        @endif

        <script src="/assets/compiled/js/app.js"></script>
        <script src="/assets/extensions/jquery/jquery.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

        <script>
            // Script untuk menjalankan Quill Editor
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Init Quill
                const el = document.getElementById('intro-editor');
                if (el) {
                    const quill = new Quill('#intro-editor', {
                        theme: 'snow',
                        placeholder: 'Tulis deskripsi event di sini...',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                [{
                                    'header': [2, 3, false]
                                }],
                                ['link', 'clean']
                            ]
                        }
                    });

                    // 2. Sync HTML content ke input hidden sebelum submit form
                    const introInput = document.getElementById('intro_html');

                    // Set initial value (jika ada error validasi, kembalikan isi lama)
                    introInput.value = quill.root.innerHTML;

                    quill.on('text-change', function() {
                        introInput.value = quill.root.innerHTML;
                    });
                }

                // 3. Validation Bootstrap Style
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms).forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
            });
        </script>
    </body>

    </html>
</x-app-layout>
