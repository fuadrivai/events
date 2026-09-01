<x-app-layout>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Manage Pricing Options - {{ $event->id }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <link rel="stylesheet" href="/assets/compiled/css/app.css">
        <link rel="stylesheet" href="/assets/compiled/css/iconly.css">
        <link
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&amp;family=Inter:wght@300;400;500;600&amp;display=swap"
            rel="stylesheet" />
        <link rel="stylesheet" href="/assets/extensions/toastify-js/src/toastify.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="/assets/static/css/school-visit.css?v=1.1.3">

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

            /* Status badges */
            .status-badge-active {
                display: inline-block;
                padding: 0.38rem 0.8rem;
                border-radius: 9999px;
                background-color: #dcfce7;
                color: #166534;
                border: 1px solid #86efac;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            .status-badge-inactive {
                display: inline-block;
                padding: 0.38rem 0.8rem;
                border-radius: 9999px;
                background-color: #f3f4f6;
                color: #374151;
                border: 1px solid #d1d5db;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.08em;
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
                        <a href="{{ route('dashboard.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="gold-divider"></div>

        <div class="" id="form-section" style="padding-top: 40px;">
            <div class="container">

                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="form-card h-100">
                            <h3 class="form-title">Tambah Opsi Baru</h3>
                            <p class="text-muted mb-4">Masukkan detail harga tiket di bawah ini.</p>

                            <form method="POST" action="{{ route('prices.store', [$orgSlug, $event->id]) }}"
                                autocomplete="off" class="needs-validation">
                                @csrf

                                <div class="form-group mb-3">
                                    <label class="form-label">Code <span class="required">*</span></label>
                                    <input type="text" name="code" id="price-code" class="form-control"
                                        placeholder="VIP" readonly required>
                                    <div class="form-text">Kode dibuat otomatis dan unik untuk tiket ini.</div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Name <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Contoh: Tiket VIP Front Row" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Amount (IDR) <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="amount" class="form-control" placeholder="50000"
                                            required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="checkActive" checked>
                                        <label class="form-check-label" for="checkActive">
                                            Aktifkan Segera
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary submit-btn">
                                        <i class="fas fa-save me-2"></i> Simpan Harga
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-8 mb-4">
                        <div class="form-card h-100">
                            <h3 class="form-title">Daftar Pilihan Harga</h3>

                            @if ($prices->count() === 0)
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <p>Belum ada pricing option yang dibuat.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order</th>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($prices as $p)
                                                <tr>
                                                    <td>{{ $p->order_index }}</td>
                                                    <td><span class="fw-bold"
                                                            data-price-code="{{ strtoupper($p->code) }}">{{ $p->code }}</span>
                                                    </td>
                                                    <td>{{ $p->name }}</td>
                                                    <td class="text-end">Rp
                                                        {{ number_format($p->amount, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($p->is_active)
                                                            <span class="status-badge-active">ACTIVE</span>
                                                        @else
                                                            <span class="status-badge-inactive">INACTIVE</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <form method="POST"
                                                            action="{{ route('prices.toggle', [$orgSlug, $event->id, $p->id]) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-secondary"
                                                                type="submit" title="Toggle Status">
                                                                <i class="fas fa-sync-alt"></i> Toggle
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="/assets/extensions/jquery/jquery.min.js"></script>
        <script src="/assets/compiled/js/app.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const codeInput = document.getElementById('price-code');
                const existingCodes = Array.from(document.querySelectorAll('[data-price-code]'))
                    .map(el => (el.dataset.priceCode || '').toUpperCase());

                function randomCode() {
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    let text = '';
                    for (let i = 0; i < 6; i++) {
                        text += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return text;
                }

                function generateUniqueCode() {
                    let candidate = randomCode();
                    let attempts = 0;

                    while (existingCodes.includes(candidate) && attempts < 20) {
                        candidate = randomCode();
                        attempts += 1;
                    }

                    return candidate;
                }

                if (codeInput) {
                    codeInput.value = generateUniqueCode();
                }
            });
        </script>
    </body>

    </html>
</x-app-layout>
