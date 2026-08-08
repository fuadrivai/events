<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $event->title }} - Mutiara Harapan Islamic School</title>
    <link rel="icon" type="image/png" href="https://mutiaraharapan.sch.id/wp-content/uploads/2024/05/cropped-cropped-hanya-logo-300x300-1-32x32.png">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&amp;family=Inter:wght@300;400;500;600&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/extensions/select2/dist/css/select2.min.css') }}">
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
        .intro-card,
        .form-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        /* Elements */
        .form-control:focus {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 0.25rem rgba(197, 160, 89, 0.25);
        }

        .btn-primary {
            background-color: #1a1a1a;
            border-color: #1a1a1a;
            padding: 10px 30px;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
            padding: 10px 30px;
        }

        .required {
            color: #dc3545;
        }

        .d-none {
            display: none !important;
        }

        /* Custom Radio Button Style */
        .radio-item-custom {
            border: 1px solid #eee;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: 0.2s;
        }

        .radio-item-custom:hover {
            background-color: #f9f9f9;
            border-color: #ddd;
        }
    </style>
</head>

<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="{{ asset('assets/images/logo.png') }}" style="height: 60px;" class="img school-logo"
                        alt="Logo">
                </div>
                <div class="col">
                    <h2 class="school-title">Mutiara Harapan Islamic School</h2>
                    <p class="school-subtitle text-muted">{{ $event->title }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="gold-divider"></div>

    @if ($event->status !== 'PUBLISHED')
        {{-- Inactive Event View --}}
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="intro-card text-center">
                        <div class="mb-4">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h2 class="page-title">Registration Closed</h2>
                        </div>
                        <div class="intro-html-content">
                            <p class="lead">We apologize, registration for <strong>{{ $event->title }}</strong> is
                                currently unavailable or has reached its deadline.</p>
                            <p class="text-muted">Please contact the school administration or visit our social media
                                channels for information regarding future registrations.</p>
                        </div>
                        <div class="mt-5">
                            <a href="https://mutiaraharapan.sch.id" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="intro-section" id="intro-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="intro-card">
                            <h2 class="page-title text-center">{{ $event->title }}</h2>

                            <div class="intro-html-content mt-4">
                                @if (!empty($event->intro_html))
                                    {!! $event->intro_html !!}
                                @else
                                    <p class="text-center">Please click the button below to proceed with your
                                        registration.</p>
                                @endif
                            </div>

                            <div class="text-center mt-5">
                                <button type="button" class="btn btn-primary" id="next-btn">
                                    <i class="fas fa-arrow-right me-2"></i>Proceed to Registration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section d-none" id="form-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="form-card">

                            {{-- Error Alert --}}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('public.event.register', $event->slug) }}"
                                class="needs-validation" novalidate>
                                @csrf

                                <h4 class="mb-3">Student/Participant Information</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Student Name <span class="required">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter your full name" required value="{{ old('name') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent's Email <i><small>(Virtual account will be sent
                                                    to this email)</small></i> <span class="required">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="example@domain.com" required value="{{ old('email') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">WhatsApp Number <span
                                                class="required">*</span></label>
                                        <input type="text" name="phone" class="form-control"
                                            placeholder="e.g. +62812345678" required value="{{ old('phone') }}">
                                    </div>
                                </div>

                                <hr class="my-4">

                                @if (isset($fields) && count($fields) > 0)
                                    <h4 class="mb-3">Additional Details</h4>
                                    <div class="row">
                                        @foreach ($fields as $f)
                                            @php
                                                $nameBase = "answers[{$f->field_key}]";
                                                $requiredAttr = $f->is_required ? 'required' : '';
                                                $opts = is_array($f->options_json) ? $f->options_json : [];
                                            @endphp

                                            <div class="col-12 mb-3">
                                                <div class="form-group">
                                                    <label class="form-label">
                                                        {{ $f->label }}
                                                        @if ($f->is_required)
                                                            <span class="required">*</span>
                                                        @endif
                                                    </label>

                                                    @if ($f->type === 'text')
                                                        <input type="text" class="form-control"
                                                            name="{{ $nameBase }}" {{ $requiredAttr }}>
                                                    @elseif ($f->type === 'textarea')
                                                        <textarea class="form-control" name="{{ $nameBase }}" rows="3" {{ $requiredAttr }}></textarea>
                                                    @elseif ($f->type === 'email')
                                                        <input type="email" class="form-control"
                                                            name="{{ $nameBase }}" {{ $requiredAttr }}>
                                                    @elseif ($f->type === 'number')
                                                        <input type="number" class="form-control"
                                                            name="{{ $nameBase }}" {{ $requiredAttr }}>
                                                    @elseif ($f->type === 'date')
                                                        <input type="date" class="form-control"
                                                            name="{{ $nameBase }}" {{ $requiredAttr }}>
                                                    @elseif ($f->type === 'select')
                                                        <select class="form-select select2"
                                                            name="{{ $nameBase }}" {{ $requiredAttr }}>
                                                            <option value="">-- Select --</option>
                                                            @foreach ($opts as $op)
                                                                <option value="{{ $op }}">
                                                                    {{ $op }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif ($f->type === 'radio')
                                                        <div class="mt-2 d-flex flex-column gap-2">
                                                            @foreach ($opts as $op)
                                                                <label class="radio-item-custom"
                                                                    style="padding: 8px 12px;">
                                                                    <input class="form-check-input mt-0 me-2"
                                                                        type="radio" name="{{ $nameBase }}"
                                                                        value="{{ $op }}"
                                                                        {{ $requiredAttr }}>
                                                                    <span>{{ $op }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @elseif ($f->type === 'checkbox')
                                                        <div class="mt-2 d-flex flex-column gap-2">
                                                            @foreach ($opts as $op)
                                                                <label class="radio-item-custom"
                                                                    style="padding: 8px 12px;">
                                                                    <input class="form-check-input mt-0 me-2"
                                                                        type="checkbox" name="{{ $nameBase }}[]"
                                                                        value="{{ $op }}">
                                                                    <span>{{ $op }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <hr class="my-4">
                                @if (isset($priceOptions) && $priceOptions->count() > 0)
                                    <h4 class="mb-3">Payment Options</h4>
                                    <div class="mb-4">
                                        <label
                                            class="form-label mb-2">{{ $event->price_question_label ?: 'Select Option' }}
                                            <span class="required">*</span></label>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach ($priceOptions as $p)
                                                <label class="radio-item-custom">
                                                    <input class="form-check-input mt-0" type="radio"
                                                        name="price_option_id" value="{{ $p->id }}" required
                                                        {{ old('price_option_id') == $p->id ? 'checked' : '' }}>
                                                    <div>
                                                        <div class="fw-bold">{{ $p->name }}</div>
                                                        <div class="text-muted small">IDR
                                                            {{ number_format($p->amount, 0, ',', '.') }}</div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                @endif
                                <div class="d-flex justify-content-between align-items-center mt-5">
                                    <button type="button" class="btn  btn-outline-secondary" id="previous-btn">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        Submit <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="alert alert-success d-none shadow-lg" id="success-message"
        style="position: fixed; top: 30px; right: 30px; z-index: 9999; max-width: 400px; border-left: 5px solid #198754;">
        <div class="d-flex align-items-start">
            <div class="me-3 fs-3"><i class="fas fa-check-circle"></i></div>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Success!</h5>
                <p class="mb-0" id="success-text"></p>
            </div>
            <button type="button" class="btn-close ms-auto"
                onclick="$('#success-message').addClass('d-none')"></button>
        </div>
    </div>

    <script src="{{ asset('assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/extensions/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Init Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Success Session Logic
            @if (session('success'))
                $('#success-text').text("{{ session('success') }}");
                $('#success-message').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: 0
                }, 'fast');

                setTimeout(function() {
                    $('#success-message').fadeOut();
                }, 10000);
            @endif

            // Error Logic: Direct to Form if validation fails
            @if ($errors->any())
                $('#intro-section').addClass('d-none');
                $('#form-section').removeClass('d-none');
            @endif

            // Navigation
            $('#next-btn').on('click', function() {
                $('#intro-section').fadeOut(300, function() {
                    $('#form-section').removeClass('d-none').hide().fadeIn(400);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'fast');
                });
            });

            $('#previous-btn').on('click', function() {
                $('#form-section').fadeOut(300, function() {
                    $('#intro-section').fadeIn(400);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'fast');
                });
            });
        });
    </script>
</body>

</html>
