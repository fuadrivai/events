<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registrations - {{ $event->title }}</title>
    
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
        .custom-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 25px; border-top: 5px solid #c5a059; }
        .stat-card { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); padding: 20px; border: 1px solid #eee; height: 100%; }
        
        /* Buttons */
        .btn-school-primary { background-color: #1a1a1a; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; transition: all 0.3s; }
        .btn-school-primary:hover { background-color: #333; color: #c5a059; transform: translateY(-1px); }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }

        /* Table Styling */
        .table-custom th { background-color: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; padding: 12px 10px; }
        .reg-code { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; border: 1px solid #eee; }
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
                            <li class="breadcrumb-item">Events</li>
                            <li class="breadcrumb-item active">Registrations</li>
                        </ol>
                    </nav>
                    <h2 class="school-title">
                        {{ $event->title }}
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard.events') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to Event
                    </a>
                </div>
                <form method="POST"
                      action="{{ route('events.registrations.export_google_sheet', ['orgSlug' => $event->slug, 'eventId' => $event->id]) }}">
                    @csrf
                    <button class="px-3 py-2 bg-green-600 text-white rounded">
                        Export ke Google Sheet
                    </button>
                </form>

            </div>
        </div>
    </div>
    
    <div class="gold-divider"></div>

    <div class="main-section">
        <div class="container">
            
            {{-- Stats Grid --}}
            <div class="row g-3 mb-4">
                {{-- Total --}}
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="text-muted small text-uppercase fw-bold mb-2">Total Registrations</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="mb-0 fw-bold text-dark">{{ $registrations->total() }}</h2>
                            <div class="text-school-gold opacity-25">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Today --}}
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="text-muted small text-uppercase fw-bold mb-2">New Today</div>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="mb-0 fw-bold text-dark">
                                {{ \App\Models\EventRegistration::where('event_id',$event->id)->whereDate('created_at', now()->toDateString())->count() }}
                            </h2>
                            <div class="text-school-gold opacity-25">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Breakdown --}}
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="text-muted small text-uppercase fw-bold mb-2">Status Breakdown</div>
                        <div class="d-flex justify-content-between align-items-center h-100">
                            <div class="text-center">
                                <span class="d-block small text-muted">Invoiced</span>
                                <span class="fw-bold fs-5 text-warning">{{ \App\Models\EventRegistration::where('event_id',$event->id)->where('status','invoiced')->count() }}</span>
                            </div>
                            <div class="vr mx-2"></div>
                            <div class="text-center">
                                <span class="d-block small text-muted">Paid</span>
                                <span class="fw-bold fs-5 text-success">{{ \App\Models\EventRegistration::where('event_id',$event->id)->where('status','paid')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="custom-card">
                        
                        {{-- Toolbar: Search & Info --}}
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                            
                            {{-- Search Form --}}
                            <form method="GET" class="flex-grow-1 w-100" style="max-width: 500px;">
                                <div class="input-group">
                                    <input type="text" name="q" value="{{ request('q') }}" 
                                           class="form-control" placeholder="Search name, email, phone...">
                                    <button class="btn btn-dark" type="submit">
                                        <i class="fas fa-search me-1"></i> Search
                                    </button>
                                    @if(request('q'))
                                        <a href="{{ route('dashboard.events.registrations.index', $event->slug) }}" class="btn btn-outline-secondary">
                                            Reset
                                        </a>
                                    @endif
                                </div>
                            </form>

                            {{-- Pagination Info --}}
                            <div class="text-muted small">
                                Showing <span class="fw-bold text-dark">{{ $registrations->count() }}</span> 
                                of <span class="fw-bold text-dark">{{ $registrations->total() }}</span>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive border rounded">
                            <table class="table table-hover table-custom mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Participant</th>
                                        <th>Contact</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrations as $r)
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $r->id }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $r->name }}</div>
                                                <div class="small text-muted">{{ $r->email }}</div>
                                            </td>
                                            <td>
                                                <div class="text-dark">{{ $r->phone }}</div>
                                            </td>
                                            <td>
                                                <span class="reg-code">{{ $r->registration_code }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($r->status){
                                                        'PAID' => 'bg-success',
                                                        'INVOICED' => 'bg-warning text-dark',
                                                        default => 'bg-primary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} rounded-pill px-3">
                                                    {{ strtoupper($r->status) }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">
                                                {{ $r->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('dashboard.registrations.show', $r->id) }}" 
                                                   class="btn btn-sm btn-outline-dark">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                                <p>Belum ada pendaftar yang ditemukan.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        <div class="mt-4 d-flex justify-content-end">
                            {{ $registrations->links() }}
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