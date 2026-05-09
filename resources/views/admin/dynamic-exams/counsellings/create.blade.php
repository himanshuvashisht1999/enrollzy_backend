@extends('admin.layouts.master')

@section('title', 'Add Counselling - ' . $dynamicExam->name)

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --app-primary: #6366f1;
            --app-primary-hover: #4f46e5;
            --app-bg: #f9fafb;
            --app-border: #e5e7eb;
            --app-text-main: #111827;
            --app-text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: var(--app-text-main);
        }

        .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid var(--app-border);
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
        }

        .input-base {
            border: 1px solid var(--app-border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s;
        }

        .input-base:focus {
            border-color: var(--app-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .label-base {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .card-custom {
            border: 1px solid var(--app-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: #fff;
        }

        .btn-primary-app {
            background: var(--app-primary);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary-app:hover {
            background: var(--app-primary-hover);
            color: #fff;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h4 fw-bold mb-0">Create New Counselling</h1>
            <p class="text-muted small mb-0">Step 1: Main Identity for {{ $dynamicExam->name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.dynamic-exams.counsellings.index', $dynamicExam->id) }}"
                class="btn btn-sm btn-outline-secondary px-3">Cancel</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-body p-4">
                    <form action="{{ route('admin.dynamic-exams.counsellings.store', $dynamicExam->id) }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="label-base">Counselling Name <span class="text-danger">*</span></label>
                                <input type="text" name="counselling_name" class="input-base"
                                    value="{{ old('counselling_name') }}" required
                                    placeholder="e.g. NEET UG Counselling {{ date('Y') }}">
                                @error('counselling_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Slug</label>
                                <input type="text" name="slug" class="input-base" value="{{ old('slug') }}"
                                    placeholder="Auto-generated if empty">
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Counselling Type <span class="text-danger">*</span></label>
                                <select name="counselling_type" class="input-base select2-single" required>
                                    <option value="Centralised" {{ old('counselling_type') == 'Centralised' ? 'selected' : '' }}>Centralised</option>
                                    <option value="State-Level" {{ old('counselling_type') == 'State-Level' ? 'selected' : '' }}>State-Level</option>
                                    <option value="Institute-Level" {{ old('counselling_type') == 'Institute-Level' ? 'selected' : '' }}>Institute-Level</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Counselling Mode</label>
                                <select name="counselling_mode" class="input-base select2-single">
                                    <option value="Online" {{ old('counselling_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Offline" {{ old('counselling_mode') == 'Offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="Hybrid" {{ old('counselling_mode') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Conducting Authority Name <span class="text-danger">*</span></label>
                                <input type="text" name="conducting_authority_name" class="input-base"
                                    value="{{ old('conducting_authority_name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Conducting Authority Type</label>
                                <select name="conducting_authority_type" class="input-base select2-single">
                                    <option value="Central Government" {{ old('conducting_authority_type') == 'Central Government' ? 'selected' : '' }}>Central Government</option>
                                    <option value="State Government" {{ old('conducting_authority_type') == 'State Government' ? 'selected' : '' }}>State Government</option>
                                    <option value="University Body" {{ old('conducting_authority_type') == 'University Body' ? 'selected' : '' }}>University Body</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="label-base">Official Website</label>
                                <input type="url" name="official_counselling_website" class="input-base"
                                    value="{{ old('official_counselling_website') }}">
                            </div>

                            <div class="col-md-12 text-end mt-5 pt-3 border-top">
                                <button type="submit" class="btn btn-primary-app">
                                    Create & Build Structure <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-single').select2({
                width: '100%'
            });
        });
    </script>
@endpush