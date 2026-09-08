<?php

namespace App\Http\Controllers\Admin\WorkManagement;

use App\Http\Controllers\Controller;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\ExternalTeam;
use App\Models\ExternalTeamMember;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Exception;

class ExternalPartnerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $query = ExternalOrganization::with(['contacts', 'teams']);

            if ($user->organization_id) {
                $query->where('organization_id', $user->organization_id);
            }
            if ($request->filled('organization_type')) {
                $query->where('organization_type', $request->organization_type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name_type', function ($row) {
                    return '<div class="d-flex flex-column">' .
                           '<a href="' . route('admin.work_management.partners.show', encrypt($row->id)) . '" class="fw-bold text-dark text-decoration-none">' . $row->name . '</a>' .
                           '<small class="text-muted">' . $row->organization_type . ' &bull; ' . ($row->contact_person ?? 'No primary contact') . '</small>' .
                           '</div>';
                })
                ->addColumn('contact_info', function ($row) {
                    $email = $row->email ? '<small class="d-block text-muted"><i class="fas fa-envelope me-1"></i>' . $row->email . '</small>' : '';
                    $phone = $row->phone ? '<small class="d-block text-muted"><i class="fas fa-phone me-1"></i>' . $row->phone . '</small>' : '';
                    return $email . $phone;
                })
                ->addColumn('contacts_count', function ($row) {
                    return '<span class="badge bg-soft-info text-info rounded-pill px-3">' . $row->contacts->count() . ' contacts</span>';
                })
                ->addColumn('teams_count', function ($row) {
                    return '<span class="badge bg-soft-primary text-primary rounded-pill px-3">' . $row->teams->count() . ' teams</span>';
                })
                ->addColumn('status', function ($row) {
                    return GetStatusBadge($row->status);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.work_management.partners.show', encrypt($row->id)) . '" class="btn btn-sm btn-soft-info"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.work_management.partners.edit', encrypt($row->id)) . '" class="btn btn-sm btn-soft-primary ms-1"><i class="fas fa-edit"></i></a>';
                    $btn .= '<form method="POST" action="' . route('admin.work_management.partners.destroy', encrypt($row->id)) . '" class="ms-1 delete-form" onsubmit="return confirm(\'Delete this partner?\')">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-soft-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name_type', 'contact_info', 'contacts_count', 'teams_count', 'status', 'action'])
                ->make(true);
        }

        return view('admin.work_management.partners.index');
    }

    public function create()
    {
        return view('admin.work_management.partners.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'organization_type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id ?? null;
            $org = ExternalOrganization::create($data);

            if ($request->filled('contact_person_name')) {
                ExternalContact::create([
                    'organization_id' => $org->organization_id,
                    'external_organization_id' => $org->id,
                    'name' => $request->contact_person_name,
                    'designation' => $request->contact_person_designation ?? 'Primary Contact',
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
            }

            return redirect()->route('admin.work_management.partners.show', encrypt($org->id))->with('success', 'External Partner added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $id = decrypt($id);
        $partner = ExternalOrganization::with(['contacts', 'teams.members', 'teams.leaderContact'])->findOrFail($id);
        return view('admin.work_management.partners.show', compact('partner'));
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $partner = ExternalOrganization::findOrFail($id);
        return view('admin.work_management.partners.edit', compact('partner'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $partner = ExternalOrganization::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'organization_type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $partner->update($request->except(['_token', '_method']));
            return redirect()->route('admin.work_management.partners.show', encrypt($partner->id))->with('success', 'External Partner updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $partner = ExternalOrganization::findOrFail($id);
        $partner->delete();
        return redirect()->route('admin.work_management.partners.index')->with('success', 'Partner deleted successfully');
    }

    public function addContact(Request $request, $orgId)
    {
        $orgId = decrypt($orgId);
        $org = ExternalOrganization::findOrFail($orgId);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ExternalContact::create([
            'organization_id' => $org->organization_id,
            'external_organization_id' => $org->id,
            'name' => $request->name,
            'designation' => $request->designation,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'External contact added successfully');
    }

    public function addTeam(Request $request, $orgId)
    {
        $orgId = decrypt($orgId);
        $org = ExternalOrganization::findOrFail($orgId);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ExternalTeam::create([
            'organization_id' => $org->organization_id,
            'external_organization_id' => $org->id,
            'name' => $request->name,
            'team_leader_contact_id' => $request->team_leader_contact_id,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'External team added successfully');
    }
}
