<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th style="width: 120px;">Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Parent Group</th>
                        <th>Normal Balance</th>
                        <th class="text-end">Current Balance</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accountsList as $account)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border fw-bold">
                                    {{ $account->account_code }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $account->name }}
                                    @if($account->is_system_account)
                                        <span class="badge bg-secondary-subtle text-secondary small ms-1" title="System Locked Account">System</span>
                                    @endif
                                </div>
                                @if($account->description)
                                    <small class="text-muted">{{ $account->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge 
                                    @if($account->account_type == 'asset') bg-primary
                                    @elseif($account->account_type == 'liability') bg-danger
                                    @elseif($account->account_type == 'equity') bg-warning text-dark
                                    @elseif($account->account_type == 'revenue') bg-success
                                    @elseif($account->account_type == 'expense') bg-info text-dark
                                    @else bg-secondary
                                    @endif
                                ">
                                    {{ strtoupper($account->account_type) }}
                                </span>
                            </td>
                            <td>
                                {{ $account->parent ? $account->parent->name : '— Root Group —' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border text-uppercase">
                                    {{ $account->normal_balance_side }}
                                </span>
                            </td>
                            <td class="text-end fw-bold {{ $account->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                                ₹{{ number_format($account->current_balance, 2) }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.accounting.reports.general_ledger', ['account_id' => $account->id]) }}" class="btn btn-outline-secondary" title="View Ledger">
                                        <i class="fas fa-list-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.accounting.chart_of_accounts.edit', $account->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$account->is_system_account)
                                        <form action="{{ route('admin.accounting.chart_of_accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No accounts in this category.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
