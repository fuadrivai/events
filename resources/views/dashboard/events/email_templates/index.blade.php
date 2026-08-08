<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Email Templates - {{ $event->title }}</title>

    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">

    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&amp;family=Inter:wght@300;400;500;600&amp;display=swap"
        rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-gold: #c5a059;
            --primary-dark: #1a1a1a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f7ff;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4,
        .school-title,
        .page-title {
            font-family: 'Playfair Display', serif;
        }

        /* Layout */
        .header-section {
            padding: 20px 0;
            background: #fff;
        }

        .school-logo {
            max-height: 80px;
            width: auto;
        }

        .gold-divider {
            height: 4px;
            background: linear-gradient(90deg, #c5a059 0%, #e6c88b 100%);
            width: 100%;
        }

        /* Cards */
        .content-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            margin-bottom: 40px;
        }

        /* Form Elements */
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 0.25rem rgba(197, 160, 89, 0.25);
        }

        .btn-primary {
            background-color: #1a1a1a;
            border-color: #1a1a1a;
            padding: 10px 30px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        /* Quill Editor Customization */
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background-color: #f8f9fa;
            border-color: #dee2e6 !important;
        }

        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            background-color: #fff;
            border-color: #dee2e6 !important;
            min-height: 250px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
        }

        /* Variables Styling */
        .var-badge {
            display: inline-block;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #d63384;
            font-size: 0.85em;
            margin: 2px;
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
                    <a href="{{ route('dashboard.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="gold-divider"></div>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- Title Section --}}
                <div class="text-center mb-5">
                    <h1 class="page-title">Email Templates configuration</h1>
                    <p class="text-muted">Event: <strong>{{ $event->title }}</strong></p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Loop Templates --}}
                @foreach ($templateKeys as $key)
                    @php $tpl = $templates[$key] ?? null; @endphp

                    <div class="content-card">
                        <form method="POST"
                            action="{{ route('admin.events.email-templates.store', ['orgSlug' => $orgSlug, 'eventId' => $event->id]) }}">
                            @csrf
                            <input type="hidden" name="key" value="{{ $key }}">

                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <h3 class="h4 mb-0 text-capitalize"><i
                                        class="fas fa-envelope-open-text me-2 text-muted"></i>
                                    {{ str_replace('_', ' ', $key) }}</h3>
                                <span class="badge bg-light text-dark border">Template Key: {{ $key }}</span>
                            </div>

                            {{-- Subject --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Email Subject</label>
                                <input type="text" name="subject" class="form-control"
                                    value="{{ old('subject', $tpl->subject ?? '') }}" required
                                    placeholder="Contoh: Pendaftaran Berhasil - {{ $event->title }}">
                            </div>

                            {{-- Body (Quill) --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Email Body (HTML)</label>

                                {{-- Hidden Input --}}
                                <input type="hidden" name="body" id="body_{{ $key }}"
                                    value="{{ old('body', $tpl->body ?? '') }}">

                                {{-- Toolbar --}}
                                <div id="toolbar_{{ $key }}">
                                    <span class="ql-formats">
                                        <select class="ql-header">
                                            <option selected></option>
                                            <option value="1"></option>
                                            <option value="2"></option>
                                        </select>
                                        <button class="ql-bold"></button>
                                        <button class="ql-italic"></button>
                                        <button class="ql-underline"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button class="ql-list" value="ordered"></button>
                                        <button class="ql-list" value="bullet"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button class="ql-link"></button>
                                    </span>
                                </div>

                                {{-- Editor --}}
                                <div id="quill_{{ $key }}">
                                    {!! old('body', $tpl->body ?? '') !!}
                                </div>
                            </div>

                            {{-- Variables Helper --}}
                            <div class="bg-light p-3 rounded mb-4 border">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <small class="text-uppercase text-muted fw-bold d-block mb-2">Core
                                            Variables</small>
                                        @verbatim
                                            <div>
                                                <span class="var-badge">{{ name }}</span>
                                                <span class="var-badge">{{ email }}</span>
                                                <span class="var-badge">{{ phone }}</span>
                                                <span class="var-badge">{{ event_title }}</span>
                                                <span class="var-badge">{{ active_until }}</span>
                                                <span class="var-badge">{{ amount }}</span>
                                                <span class="var-badge">{{ invoice_url }}</span>
                                            </div>
                                        @endverbatim
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-uppercase text-muted fw-bold d-block mb-2">Form
                                            Answers</small>
                                        @if (($fields ?? collect())->count() === 0)
                                            <small class="text-muted fst-italic">Belum ada field tambahan.</small>
                                        @else
                                            <div>
                                                @foreach ($fields as $f)
                                                    <span class="var-badge" title="{{ $f->label }}">
                                                        @php echo e('{{ answers . ' . $f->field_key . ' }}'); @endphp
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2 text-muted" style="font-size: 11px;">
                                    <i class="fas fa-info-circle me-1"></i> Klik variabel untuk menyalin (opsional,
                                    perlu JS tambahan) atau ketik manual di editor.
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <button type="submit" formtarget="_blank"
                                        formaction="{{ route('admin.events.email-templates.preview', ['orgSlug' => $orgSlug, 'eventId' => $event->id]) }}"
                                        class="btn btn-outline-secondary">
                                        <i class="fas fa-external-link-alt me-2"></i> Preview
                                    </button>
                                </div>
                                <button type="submit" class="btn btn-success submit-btn" id="submit-btn">
                                    <span class="submit-text"><i class="fas fa-save me-2"></i> Save Template</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    <div class="text-center mb-5 text-muted small">
        © {{ date('Y') }} Mutiara Harapan Islamic School. Internal System.
    </div>

    <script src="{{ asset('assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil key template dari PHP
            const keys = @json($templateKeys);

            keys.forEach(function(key) {
                const editorEl = document.getElementById('quill_' + key);
                const toolbarEl = document.getElementById('toolbar_' + key);
                const hiddenEl = document.getElementById('body_' + key);

                if (!editorEl || !toolbarEl || !hiddenEl) return;

                // Init Quill dengan tema yang bersih
                const quill = new Quill(editorEl, {
                    theme: 'snow',
                    modules: {
                        toolbar: toolbarEl
                    },
                    placeholder: 'Tulis isi email di sini...'
                });

                // Set value awal jika ada error/edit
                const initialHtml = hiddenEl.value || '';
                if (initialHtml) {
                    quill.root.innerHTML = initialHtml;
                }

                // Saat form disubmit, salin HTML Quill ke hidden input
                const form = editorEl.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        hiddenEl.value = quill.root.innerHTML;
                    });
                }
            });
        });
    </script>
</body>

</html>
