<?php

namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Services\Quality\QualitySync;

/**
 * One-page data update for Rankings & Accreditation. After the SQL file has created the tables, open this page,
 * check the preview, press "Run update". Safe to run again whenever the database has changed.
 */
class SetupController extends Controller
{
    public function index(QualitySync $sync)
    {
        $missing = $sync->missingStructure();
        return view('admin.quality.setup', [
            'missing' => $missing,
            'steps' => $missing ? [] : $sync->preview(),
            'results' => session('setup_results'),
        ]);
    }

    public function run(QualitySync $sync)
    {
        if ($missing = $sync->missingStructure()) {
            return back()->with('error', 'The database structure is not ready yet. Run the SQL file first. Missing: ' . implode(', ', $missing));
        }
        set_time_limit(300);
        $results = $sync->run(auth('admin')->id());
        return redirect()->route('admin.quality.setup')->with('setup_results', $results)->with('success', 'Update finished.');
    }
}
