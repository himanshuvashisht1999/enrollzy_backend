<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * OTP security: who requests the website's sign-in OTPs, the patterns that look like spam, and IP addresses restricted
 * for good. The website logs every request (otp_requests) and applies the limits (enrollzy_frontend App\Support\OtpGuard);
 * a blocked IP sees only a notice there (BlockedIp middleware).
 */
class OtpSecurityController extends Controller
{
    /** what makes an IP suspicious: [label, threshold] */
    public const FLAGS = [
        'daily_days' => ['Hit the 24-hour limit on %d different days (last 30 days)', 3],
        'mobiles_24h' => ['Asked OTPs for %d different numbers in 24 hours', 5],
        'refused_7d' => ['Kept trying %d times while restricted (last 7 days)', 20],
        'active_days' => ['Requested OTPs on %d of the last 7 days', 5],
    ];
    private const SENT = ['sent', 'widget'];
    private const REFUSED = ['paused', 'daily', 'blocked'];

    public function index(Request $request)
    {
        $blocked = DB::table('blocked_ips as b')->leftJoin('admin as a', 'a.id', '=', 'b.blocked_by')
            ->orderByDesc('b.created_at')->get(['b.*', 'a.name as admin_name']);
        $blockedSet = $blocked->pluck('ip')->flip();

        // one row per IP seen in the last 30 days, with the numbers the flags use
        $d1 = now()->subDay(); $d7 = now()->subDays(7); $d30 = now()->subDays(30);
        $sent = "'" . implode("','", self::SENT) . "'"; $ref = "'" . implode("','", self::REFUSED) . "'";
        $ips = DB::table('otp_requests')->where('created_at', '>=', $d30)->groupBy('ip')->selectRaw("ip,
            SUM(outcome IN ($sent) AND created_at >= ?) sent_24h,
            SUM(outcome IN ($sent) AND created_at >= ?) sent_7d,
            SUM(outcome IN ($ref) AND created_at >= ?) refused_7d,
            COUNT(DISTINCT CASE WHEN outcome = 'daily' THEN DATE(created_at) END) daily_days,
            COUNT(DISTINCT CASE WHEN outcome IN ($sent) AND created_at >= ? THEN mobile END) mobiles_24h,
            COUNT(DISTINCT CASE WHEN created_at >= ? THEN DATE(created_at) END) active_days,
            COUNT(*) total_30d, MIN(created_at) first_seen, MAX(created_at) last_seen", [$d1, $d7, $d7, $d1, $d7])
            ->orderByDesc('last_seen')->limit(500)->get()
            ->map(function ($r) use ($blockedSet) {
                $r->flags = collect(self::FLAGS)->filter(fn ($f, $k) => (int) $r->{$k} >= $f[1])->map(fn ($f, $k) => sprintf($f[0], (int) $r->{$k}))->values()->all();
                $r->blocked = isset($blockedSet[$r->ip]);
                return $r;
            });
        $suspicious = $ips->filter(fn ($r) => $r->flags && !$r->blocked)->values();

        // numbers getting OTPs from many IPs (someone flooding a person's phone)
        $targets = DB::table('otp_requests')->where('created_at', '>=', $d1)->whereNotNull('mobile')->groupBy('mobile')
            ->selectRaw('mobile, COUNT(*) n, COUNT(DISTINCT ip) ips, MAX(created_at) last_seen')->havingRaw('COUNT(DISTINCT ip) >= 3 OR COUNT(*) >= 10')
            ->orderByDesc('n')->limit(50)->get();

        $log = null;
        if ($ip = trim((string) $request->query('ip'))) {
            $log = DB::table('otp_requests')->where('ip', $ip)->orderByDesc('created_at')->limit(200)->get();
        }
        $today = DB::table('otp_requests')->where('created_at', '>=', $d1)->selectRaw("SUM(outcome IN ($sent)) sent, SUM(outcome IN ($ref)) refused, COUNT(DISTINCT ip) ips")->first();

        return view('admin.otp-security.index', compact('blocked', 'ips', 'suspicious', 'targets', 'log', 'ip', 'today'));
    }

    public function block(Request $request)
    {
        $data = $request->validate(['ip' => ['required', 'ip'], 'reason' => ['nullable', 'string', 'max:500']]);
        if ($data['ip'] === $request->ip()) {
            return back()->with('error', 'That is your own IP address: blocking it would lock you out of the website.');
        }
        DB::table('blocked_ips')->updateOrInsert(['ip' => $data['ip']], [
            'reason' => trim((string) ($data['reason'] ?? '')) ?: null, 'blocked_by' => auth('admin')->id(), 'updated_at' => now(), 'created_at' => now(),
        ]);
        return back()->with('success', $data['ip'] . ' is restricted permanently. It now sees only the restriction notice on the website.');
    }

    public function unblock(int $id)
    {
        $row = DB::table('blocked_ips')->find($id);
        if (!$row) return back()->with('error', 'That IP is not blocked.');
        DB::table('blocked_ips')->where('id', $id)->delete();
        return back()->with('success', $row->ip . ' is unblocked. Its old OTP requests still count towards the normal limits.');
    }
}
