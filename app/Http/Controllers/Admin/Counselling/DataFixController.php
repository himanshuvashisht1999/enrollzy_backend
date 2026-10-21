<?php

namespace App\Http\Controllers\Admin\Counselling;

use App\Http\Controllers\Controller;
use App\Services\Counselling\DataFixes;
use Illuminate\Http\Request;

/** /admin/counselling/data-fixes: one-time data corrections, previewed and then run with one click. */
class DataFixController extends Controller
{
    public function index(DataFixes $fixes)
    {
        return view('admin.counselling.data-fixes', ['fixes' => $fixes->all()]);
    }

    public function run(Request $request, DataFixes $fixes)
    {
        $result = $fixes->run($request->user('admin')?->id, $request->user('admin') ?? $request->user());
        if (!$result) {
            return back()->with('success', 'Nothing to do: every data fix has already run.');
        }
        return back()->with('success', 'Data fixes run: ' . collect($result)->map(fn ($n, $k) => "$k ($n changed)")->implode(', ') . '.');
    }
}
