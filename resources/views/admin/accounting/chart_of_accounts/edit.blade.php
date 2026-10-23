@extends('admin.layouts.master')

@section('title', 'Edit Chart of Account')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.accounting.chart_of_accounts.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Accounts List
        </a>
        <h3 class="fw-bold mb-1">Edit Account: {{ $account->account_code }} - {{ $account->name }}</h3>
        <p class="text-muted small mb-0">Modify account settings and description.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.accounting.chart_of_accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Code</label>
                        <input type="text" name="account_code" class="form-control font-monospace" value="{{ old('account_code', $account->account_code) }}" {{ $account->is_system_account ? 'readonly' : 'required' }}>
                        @if($account->is_system_account)
                            <small class="text-muted text-warning"><i class="fas fa-lock me-1"></i> System locked account code cannot be modified.</small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $account->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Account Type</label>
                        @if($account->is_system_account)
                            <input type="text" class="form-control text-uppercase" value="{{ $account->account_type }}" readonly>
                        @else
                            <select name="account_type" class="form-select" required>
                                <option value="asset" {{ old('account_type', $account->account_type) == 'asset' ? 'selected' : '' }}>Asset (1000s)</option>
                                <option value="liability" {{ old('account_type', $account->account_type) == 'liability' ? 'selected' : '' }}>Liability (2000s)</option>
                                <option value="equity" {{ old('account_type', $account->account_type) == 'equity' ? 'selected' : '' }}>Equity & Capital (3000s)</option>
                                <option value="revenue" {{ old('account_type', $account->account_type) == 'revenue' ? 'selected' : '' }}>Revenue / Income (4000s)</option>
                                <option value="expense" {{ old('account_type', $account->account_type) == 'expense' ? 'selected' : '' }}>Expense (5000s)</option>
                                <option value="tax" {{ old('account_type', $account->account_type) == 'tax' ? 'selected' : '' }}>Tax (6000s)</option>
                            </select>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Parent Group Account</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— None (Top Level Group) —</option>
                            @foreach($parentAccounts as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->account_code }} - {{ $parent->name }} ({{ strtoupper($parent->account_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Description / Purpose</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $account->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $account->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">Account Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.accounting.chart_of_accounts.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
