<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Question - {{ $event->title }}</title>
    
    <link rel="stylesheet" href="/assets/compiled/css/app.css">
    <link rel="stylesheet" href="/assets/compiled/css/iconly.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- INLINE STYLE SESUAI TEMPLATE --}}
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f2f7ff; }
        h1, h2, h3, h4, .school-title, .page-title, .form-title { font-family: 'Playfair Display', serif; }
        
        /* Header & Logo */
        .header-section { background: white; padding: 20px 0; }
        .school-title { font-weight: 700; color: #333; margin-bottom: 5px; font-size: 1.5rem; }
        .school-subtitle { color: #666; font-size: 0.9rem; margin-bottom: 0; }
        .school-logo { max-height: 80px; width: auto; }

        /* Gold Divider */
        .gold-divider { height: 4px; background: linear-gradient(90deg, #c5a059 0%, #e6c88b 100%); width: 100%; }

        /* Form Card Styling */
        .form-section { padding: 40px 0; min-height: 80vh; }
        .form-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border-top: 5px solid #c5a059; }
        .form-title { color: #333; margin-bottom: 30px; text-align: center; font-weight: 600; }
        
        /* Form Elements */
        .form-label { font-weight: 500; color: #444; margin-bottom: 8px; }
        .required { color: #dc3545; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 15px; border: 1px solid #dee2e6; }
        .form-control:focus, .form-select:focus { border-color: #c5a059; box-shadow: 0 0 0 0.25rem rgba(197, 160, 89, 0.25); }
        .form-check-input:checked { background-color: #c5a059; border-color: #c5a059; }
        
        /* Buttons */
        .btn-school-primary { background-color: #1a1a1a; color: #fff; border: none; padding: 10px 30px; border-radius: 8px; font-weight: 600; transition: all 0.3s; }
        .btn-school-primary:hover { background-color: #333; color: #c5a059; transform: translateY(-1px); }
        .btn-outline-secondary { border-radius: 8px; padding: 10px 25px; }
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
                        Edit Question: {{ $field->label }}
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard.events.fields', $event) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back
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
                    
                    {{-- Error Alerts --}}
                    @if ($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 mb-4" role="alert">
                        <strong><i class="fas fa-exclamation-circle me-2"></i> Please correct the following errors:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="form-card">
                        <h3 class="form-title">Edit Question</h3>

                        @php
                            $optionsText = '';
                            if (is_array($field->options_json)) $optionsText = implode("\n", $field->options_json);
                        @endphp

                        <form method="POST" action="{{ route('dashboard.events.fields.update', [$event, $field]) }}" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')

                            <h4 class="mb-4 text-muted border-bottom pb-2 fs-5">Field Configuration</h4>

                            {{-- Label --}}
                            <div class="mb-3">
                                <label class="form-label">Question Label <span class="required">*</span></label>
                                <input type="text" name="label" value="{{ old('label', $field->label) }}" 
                                       class="form-control" placeholder="e.g. Full Name" required>
                            </div>

                            {{-- Type --}}
                            <div class="mb-3">
                                <label class="form-label">Input Type</label>
                                <select name="type" class="form-select">
                                    @php $types = ['text','textarea','select','radio','checkbox','email','phone','number','date']; @endphp
                                    @foreach ($types as $t)
                                        <option value="{{ $t }}" @selected(old('type', $field->type)===$t)>{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Options --}}
                            <div class="mb-3">
                                <label class="form-label">Options (for Select/Radio/Checkbox)</label>
                                <textarea name="options" rows="4" class="form-control" 
                                          placeholder="Enter options, one per line">{{ old('options', $optionsText) }}</textarea>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Leave empty if type is not select, radio, or checkbox.
                                </div>
                            </div>

                            <div class="row mb-4">
                                {{-- Required Checkbox --}}
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded bg-light">
                                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1"
                                               @checked(old('is_required', $field->is_required))>
                                        <label class="form-check-label fw-bold" for="is_required">
                                            Required Field
                                        </label>
                                        <div class="small text-muted mt-1">User must fill this field.</div>
                                    </div>
                                </div>

                                {{-- Active Checkbox --}}
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="form-check p-3 border rounded bg-light">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               @checked(old('is_active', $field->is_active))>
                                        <label class="form-check-label fw-bold" for="is_active">
                                            Active
                                        </label>
                                        <div class="small text-muted mt-1">Visible on the public form.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Field Key Info --}}
                            <div class="alert alert-light border d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-code text-muted me-2"></i>
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold">System Key:</span>
                                    <code class="ms-2 fw-bold text-dark">{{ $field->field_key }}</code>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('dashboard.events.fields', $event) }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-school-primary shadow">
                                    <i class="fas fa-save me-2"></i> Update Question
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
</body>
</html>