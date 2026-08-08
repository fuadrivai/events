<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration Detail - {{ $registration->event->title }}</title>
    
    <link rel="stylesheet" href="/assets/compiled/css/app.css">
    <link rel="stylesheet" href="/assets/compiled/css/iconly.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- INLINE STYLE SESUAI TEMPLATE --}}
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f2f7ff; }
        h1, h2, h3, h4, .school-title, .page-title, .card-title { font-family: 'Playfair Display', serif; }
        
        /* Header & Logo */
        .header-section { background: white; padding: 20px 0; }
        .school-title { font-weight: 700; color: #333; margin-bottom: 5px; font-size: 1.5rem; }
        .school-subtitle { color: #666; font-size: 0.9rem; margin-bottom: 0; }
        .school-logo { max-height: 80px; width: auto; }

        /* Gold Divider */
        .gold-divider { height: 4px; background: linear-gradient(90deg, #c5a059 0%, #e6c88b 100%); width: 100%; }

        /* Content & Cards */
        .main-section { padding: 40px 0; min-height: 80vh; }
        .custom-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px; border-top: 5px solid #c5a059; margin-bottom: 20px; }
        
        /* Buttons */
        .btn-school-primary { background-color: #1a1a1a; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; transition: all 0.3s; }
        .btn-school-primary:hover { background-color: #333; color: #c5a059; transform: translateY(-1px); }
        .btn-outline-secondary { border-radius: 8px; padding: 8px 20px; }

        /* Reg Info Styles */
        .info-label { color: #6c757d; font-size: 0.85rem; margin-bottom: 0.2rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .info-value { color: #212529; font-weight: 500; font-size: 1rem; }
        .reg-code { font-family: monospace; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; border: 1px solid #dee2e6; color: #333; font-weight: bold; }
        
        /* Table Styles */
        .table-custom th { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
    </style>
</head>

<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/assets/images/logo.png" class="school-logo" alt="Logo" onerror="this.style.display='none'">
                </div>
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small text-muted">
                            <li class="breadcrumb-item">Dashboard</li>
                            <li class="breadcrumb-item">Registrations</li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </nav>
                    <h2 class="school-title">
                        {{ $registration->event->title }}
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard.events.registrations.index', $registration->event->slug) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="gold-divider"></div>

    <div class="main-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    {{-- Summary Card --}}
                    <div class="custom-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Participant Information</h4>
                            <div class="d-flex gap-2">
                                {{-- Tombol Edit/Print bisa ditambahkan di sini --}}
                            </div>
                        </div>

                        <div class="row g-4">
                            {{-- Kolom Kiri: Biodata --}}
                            <div class="col-md-7 border-end">
                                <div class="mb-3">
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value fs-4">{{ $registration->name }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value">
                                            <a href="mailto:{{ $registration->email }}" class="text-decoration-none text-dark">
                                                <i class="far fa-envelope me-2 text-muted"></i>{{ $registration->email }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value">
                                            <a href="https://wa.me/{{ $registration->phone }}" target="_blank" class="text-decoration-none text-dark">
                                                <i class="fab fa-whatsapp me-2 text-muted"></i>{{ $registration->phone }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Status & Meta --}}
                            <div class="col-md-5 ps-md-4">
                                <div class="mb-3">
                                    <div class="info-label">Registration Code</div>
                                    <div class="mt-1">
                                        <span class="reg-code">{{ $registration->registration_code }}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="info-label">Status</div>
                                    <div class="mt-1">
                                        @php
                                            $badgeClass = match($registration->status){
                                                'paid' => 'bg-success',
                                                'pending' => 'bg-warning text-dark',
                                                default => 'bg-primary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">
                                            {{ strtoupper($registration->status) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <div class="info-label">Registered At</div>
                                    <div class="info-value small text-muted">
                                        <i class="far fa-calendar-alt me-2"></i>{{ $registration->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Answers Card --}}
                    <div class="custom-card">
                        <h4 class="card-title mb-4">Form Answers</h4>
                        
                        <div class="table-responsive border rounded-3">
                            <table class="table table-hover table-custom mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40%;" class="ps-4">Question / Field</th>
                                        <th class="ps-4">Answer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registration->answers as $a)
                                        <tr>
                                            <td class="ps-4 align-top text-muted fw-bold">
                                                {{ $a->field_key }}
                                                {{-- Jika Anda ingin menampilkan Label asli (bukan key), Anda perlu meload relasi field di controller --}}
                                            </td>
                                            <td class="ps-4 align-top">
                                                <div class="text-dark" style="white-space: pre-wrap;">{{ $a->value }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">
                                                <i class="fas fa-comment-slash me-2"></i> Tidak ada jawaban form tambahan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="/assets/extensions/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>