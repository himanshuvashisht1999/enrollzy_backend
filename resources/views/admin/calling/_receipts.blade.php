{{-- Read receipts of a notice (loaded into a modal). --}}
<div class="cc">
    <p class="sm muted mb-2">{{ $notice->title }}</p>
    <table><thead><tr><th>Recipient</th><th>Status</th></tr></thead><tbody>
    @forelse($rows as $r)
        <tr><td><div class="row">@include('admin.calling._av', ['p' => $r['p'], 'size' => 'sm']){{ $r['p']['name'] }}</div></td>
            <td>@if($r['at'])<span class="yes">✓ {{ \Carbon\Carbon::parse($r['at'])->format('j M, g:i A') }}</span>@else<span class="pill st-warn">Pending</span>@endif</td></tr>
    @empty
        <tr><td colspan="2" class="text-center muted">No recipients.</td></tr>
    @endforelse
    </tbody></table>
</div>
