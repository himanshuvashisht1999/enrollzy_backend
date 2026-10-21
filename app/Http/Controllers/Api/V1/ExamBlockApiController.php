<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            $items = $this->presenter->examCounsellings($exam->id);

            return ['exam' => ['name' => $exam->name, 'slug' => $exam->slug], 'counsellings' => $items];
        });

        if ($request->input('format') === 'html') {
            return response(view('public.counselling.blocks.exam-counselling', $data)->render(), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        return response()->json($data);
    }
}
