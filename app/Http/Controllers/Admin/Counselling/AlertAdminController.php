<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Models\Counselling\Alert;
use App\Models\Counselling\AlertDelivery;
use App\Models\Counselling\Subscriber;
use App\Models\Counselling\Subscription;
use App\Services\Counselling\Alerts\AlertScanner;
use App\Services\Counselling\Alerts\AlertSender;
use App\Services\Counselling\WhatsApp\WhatsApp;
use Illuminate\Support\Facades\DB;

/** Staff view of the follow / alert system: who follows what, what went out, what failed. */
class AlertAdminController extends Controller
{
    public function index()
    {
        $alerts = Alert::withCount([
            'deliveries as sent_count' => fn ($q) => $q->where('status', 'sent'),
            'deliveries as failed_count' => fn ($q) => $q->where('status', 'failed'),
            'deliveries as pending_count' => fn ($q) => $q->where('status', 'pending'),
            'deliveries as skipped_count' => fn ($q) => $q->where('status', 'skipped'),
        ])->latest('id')->paginate(25);

        $targets = [];
        foreach ($alerts as $a) {
            $targets[$a->id] = $a->target_type === 'counselling'
                ? DB::table('cns_counsellings')->where('id', $a->target_id)->value('name')
                : DB::table('exm_exams')->where('id', $a->target_id)->value('name');
        }

        return view('admin.counselling.alerts.index', [
            'alerts' => $alerts, 'targets' => $targets,
            'stats' => [
                'subscribers' => Subscriber::count(),
                'email_confirmed' => Subscriber::whereNotNull('email_confirmed_at')->count(),
                'whatsapp_opted_in' => Subscriber::whereNotNull('whatsapp_opt_in_at')->count(),
                'follows' => Subscription::whereNotNull('confirmed_at')->count(),
                'awaiting_confirmation' => Subscription::whereNull('confirmed_at')->count(),
                'pending' => AlertDelivery::where('status', 'pending')->count(),
                'failed' => AlertDelivery::where('status', 'failed')->count(),
            ],
            'failures' => AlertDelivery::with(['alert', 'subscriber'])->where('status', 'failed')->latest('id')->limit(15)->get(),
            'whatsapp' => ['driver' => config('cns.whatsapp.driver'), 'configured' => WhatsApp::provider()->configured()],
        ]);
    }

    /** Run the scanner and sender now instead of waiting for the schedule. */
    public function run(AlertScanner $scanner, AlertSender $sender)
    {
        $new = $scanner->scan();
        $r = $sender->send();
        return back()->with('success', "$new new alert(s). Deliveries: {$r['sent']} sent, {$r['failed']} failed, {$r['skipped']} skipped.");
    }

    public function retry(AlertSender $sender)
    {
        $n = $sender->retryFailed();
        return back()->with('success', "$n failed delivery(ies) queued again (up to 3 attempts each).");
    }
}
