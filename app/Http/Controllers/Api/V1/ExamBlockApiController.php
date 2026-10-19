<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Counselling\CounsellingExam;
use App\Services\Counselling\PublicPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** "Counselling after this exam": the block for an exam page. JSON, or ready-made HTML with ?format=html. */
class ExamBlockApiController extends Controller
{
    public function __construct(private PublicPresenter $presenter)
    {
    }

    public function show(Request $request, string $slug)
    {
        $exam = DB::table('exm_exams')->where('slug', $slug)->whereNull('deleted_at')->where('visibility', 'public')->where('status', true)->first();
        abort_unless($exam, 404, 'Exam not found.');

        $data = Cache::remember('cns:api:exam-block:' . $exam->id, config('cns.cache_ttl'), function () use ($exam) {
            $items = CounsellingExam::where('exam_id', $exam->id)->orderBy('sort_order')->get()
                ->map(function ($m) {
                    $c = $this->presenter->visible()->with('conductingBody')->find($m->counselling_id);
                    if (!$c) return null;
                    $item = $this->presenter->listItem($c);
                    $cycle = $this->presenter->cycleFor($c);
                    $bar = $cycle ? $cycle->statusBar() : null;
                    return $item + [
                        'role' => $m->role, 'seat_scope_note' => $m->seat_scope_note,
                        'status' => $cycle ? (in_array($cycle->cycle_status, ['postponed', 'cancelled'])
                            ? ['state' => $cycle->cycle_status, 'text' => ucfirst($cycle->cycle_status) . ($cycle->status_note ? ': ' . $cycle->status_note : '')]
                            : ($bar ? ['state' => 'scheduled', 'text' => $bar['round']->name . ': ' . collect($bar['items'])->map(fn ($s, $l) => "$l - {$s['label']}")->implode(', ')]
                                : ['state' => 'to_be_announced', 'text' => 'Schedule to be announced']))
                            : ['state' => 'to_be_announced', 'text' => 'Schedule to be announced'],
                    ];
                })->filter()->values()->all();

            return ['exam' => ['name' => $exam->name, 'slug' => $exam->slug], 'counsellings' => $items];
        });

        if ($request->input('format') === 'html') {
            return response(view('public.counselling.blocks.exam-counselling', $data)->render(), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        return response()->json($data);
    }
}
