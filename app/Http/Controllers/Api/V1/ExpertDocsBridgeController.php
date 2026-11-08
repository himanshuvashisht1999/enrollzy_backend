<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Experts\Bridge;
use App\Support\Experts\Docs;
use Illuminate\Http\Request;

/**
 * /api/v1/expert-docs: the website's way to the expert documents kept here (it cannot reach this site's folders on
 * the live server). Every request must be signed (App\Support\Experts\Bridge); anything else is 404, so the address
 * gives nothing away. GET returns a file, POST stores an upload in a folder, DELETE removes a file.
 */
class ExpertDocsBridgeController extends Controller
{
    public function show(Request $request)
    {
        abort_unless(Bridge::valid('GET', $request->query('p'), $request->query('e'), $request->query('s')), 404);
        return Docs::response($request->query('p'));
    }

    public function store(Request $request)
    {
        $folder = (string) $request->query('p');
        abort_unless(Bridge::valid('POST', $folder, $request->query('e'), $request->query('s')), 404);
        abort_unless(preg_match('#^(applications|experts)/\d+$#', $folder), 422);
        // the website has checked the file; check its content again here
        $request->validate(['file' => 'required|file|mimes:' . Docs::MIMES . '|max:' . Docs::MAX_KB]);
        return response()->json(['path' => Docs::store($request->file('file'), $folder)]);
    }

    public function destroy(Request $request)
    {
        abort_unless(Bridge::valid('DELETE', $request->query('p'), $request->query('e'), $request->query('s')), 404);
        Docs::delete($request->query('p'));
        return response()->json(['ok' => true]);
    }
}
