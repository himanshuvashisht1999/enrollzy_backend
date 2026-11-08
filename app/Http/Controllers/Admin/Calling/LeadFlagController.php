<?php

namespace App\Http\Controllers\Admin\Calling;

use App\Http\Controllers\Controller;
use App\Support\Calling\LeadFlags;
use Illuminate\Http\Request;

/**
 * The call form's "Escalate to my team lead" / "Mark as important" section reads a lead's flags here; the team lead
 * resolves an escalation from their dashboard. Setting the flags happens with the call itself (CallingController::store).
 */
class LeadFlagController extends Controller
{
    public function __construct(private LeadFlags $flags)
    {
    }

    public function show(int $customer)
    {
        return response()->json($this->flags->state($customer));
    }

    public function resolve(Request $request, int $escalation)
    {
        $note = trim(strip_tags((string) $request->input('resolution')));
        if (!$this->flags->resolve($escalation, $request->user(), $note !== '' ? $note : null)) {
            return back()->with('error', 'That escalation is already closed, or it is not yours to resolve.');
        }
        return back()->with('success', 'Escalation resolved.');
    }
}
