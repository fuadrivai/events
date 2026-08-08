<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Builder - {{ $event->title }}</title>
    
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

        /* Card Styling */
        .main-section { padding: 40px 0; min-height: 80vh; }
        .custom-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px; border-top: 5px solid #c5a059; }
        
        /* Buttons */
        .btn-school-primary { background-color: #1a1a1a; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; transition: all 0.3s; }
        .btn-school-primary:hover { background-color: #333; color: #c5a059; transform: translateY(-1px); }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }

        /* Table Styling */
        .table-custom th { background-color: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; padding: 12px 10px; }
        
        /* Drag Handle */
        .drag-handle { cursor: grab; color: #adb5bd; transition: color 0.2s; }
        .drag-handle:hover { color: #c5a059; }
        .drag-handle:active { cursor: grabbing; }

        /* Status Toast */
        #reorder-status { position: fixed; bottom: 30px; right: 30px; z-index: 9999; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
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
                    <h2 class="school-title">
                        Form Builder
                    </h2>
                    <p class="school-subtitle">
                        Event: {{ $event->title }}
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to Event
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="gold-divider"></div>

    <div class="main-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    
                    <div class="custom-card">
                        
                        {{-- Header Card --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title mb-0">Manage Questions</h3>
                            
                            <a href="{{ route('dashboard.events.fields.create', $event) }}" class="btn btn-school-primary shadow-sm">
                                <i class="fas fa-plus me-2"></i> Add Question
                            </a>
                        </div>

                        {{-- Alert Success --}}
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        {{-- Table Section --}}
                        <div class="table-responsive border rounded-3">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center"></th>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th class="text-center">Required</th>
                                        <th>Options</th>
                                        <th class="text-center" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fields-sortable">
                                    @forelse ($fields as $f)
                                        <tr data-id="{{ $f->id }}" class="bg-white">
                                            {{-- Drag Handle --}}
                                            <td class="text-center bg-light">
                                                <div class="drag-handle p-2" title="Drag to reorder">
                                                    <i class="fas fa-grip-vertical"></i>
                                                </div>
                                            </td>
                                            
                                            <td class="fw-bold text-dark">{{ $f->label }}</td>
                                            
                                            <td><span class="badge bg-light text-dark border">{{ $f->type }}</span></td>
                                            
                                            <td class="text-center">
                                                @if($f->is_required)
                                                    <i class="fas fa-check text-success"></i>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td class="small text-muted">
                                                @if (is_array($f->options_json) && count($f->options_json))
                                                    {{ implode(', ', $f->options_json) }}
                                                @else
                                                    <span class="text-light-gray">-</span>
                                                @endif
                                            </td>
                                            
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('dashboard.events.fields.edit', [$event, $f]) }}" 
                                                       class="btn btn-outline-primary btn-action" title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
        
                                                    <form method="POST" action="{{ route('dashboard.events.fields.destroy', [$event, $f]) }}" 
                                                          onsubmit="return confirm('Hapus pertanyaan ini?')" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-action" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fas fa-clipboard-list fa-2x mb-3 opacity-25"></i>
                                                <p class="mb-0">Belum ada pertanyaan dibuat.</p>
                                                <small>Klik tombol "Add Question" di atas.</small>
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

    <div id="reorder-status" class="toast align-items-center text-white bg-dark border-0 hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-spinner fa-spin me-2" id="status-icon"></i> <span id="status-text">Saving...</span>
            </div>
        </div>
    </div>

    <script src="/assets/extensions/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('fields-sortable');
        const statusEl = document.getElementById('reorder-status');
        const statusText = document.getElementById('status-text');
        const statusIcon = document.getElementById('status-icon');
        let bsToast = new bootstrap.Toast(statusEl, { delay: 2000 });

        function showStatus(text, type = 'loading') {
            statusText.textContent = text;
            statusEl.classList.remove('bg-success', 'bg-danger', 'bg-dark');
            
            if(type === 'success') {
                statusEl.classList.add('bg-success');
                statusIcon.className = 'fas fa-check-circle me-2';
                bsToast.show();
            } else if (type === 'error') {
                statusEl.classList.add('bg-danger');
                statusIcon.className = 'fas fa-times-circle me-2';
                bsToast.show();
            } else {
                statusEl.classList.add('bg-dark');
                statusIcon.className = 'fas fa-spinner fa-spin me-2';
                bsToast.show();
            }
        }

        if (tbody) {
            new Sortable(tbody, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-light',
                onEnd: async () => {
                    const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => tr.dataset.id);

                    showStatus('Saving order...', 'loading');
                    
                    try {
                        const res = await fetch("{{ route('dashboard.events.fields.reorder', $event) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({ ids })
                        });

                        if (!res.ok) {
                            showStatus('Failed to save', 'error');
                            return;
                        }

                        showStatus('Order saved', 'success');
                    } catch (e) {
                        showStatus('Connection error', 'error');
                    }
                }
            });
        }
    });
    </script>
</body>
</html>