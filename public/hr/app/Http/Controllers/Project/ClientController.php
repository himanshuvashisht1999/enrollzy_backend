<?php

namespace App\Http\Controllers\Project;

use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if(Auth::guard('admin')->user()->role === 'superadmin'){
                $data = Client::get();
            }else{
                $data = Client::where('organization_id', Auth::guard('admin')->user()->organization_id)->get();
            }
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('created_at', function ($row) {
                    $created_at = '<p class="text-sm">' . date('h:i A - d M, Y ', strtotime($row->created_at)) . '</p>';
                    return $created_at;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex">';

                    $btn .= '<a href="' . route('admin.client.edit', encrypt($row->id)) . '" class="btn btn-sm"><i
                    class="fa fa-edit text-success"></i></a>';
                    $btn .= ' | ';

                    $btn .= '<form method="POST" action="' . route('admin.client.destroy', encrypt($row->id)) . '" class="m-0 p-0">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <button type="submit" class="btn btn-sm confirm-button"><i
                        class="fa fa-trash text-danger"></i></button>
                        </form>';

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['name', 'created_at', 'action'])
                ->make(true);
        }

        return view('project.client.index');
    }

    public function create()
    {
        return view('project.client.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $createClient = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'landmark' => $request->landmark,
            'city' => $request->city,
            'state' => $request->state,
            'pin_code' => $request->pin_code,
            'profile_image' => $request->profile_image,
            'description' => $request->description,
            'organization_id' => Auth::guard('admin')->user()->organization_id,
        ];
        try {
            Client::create($createClient);
            return redirect(route('admin.client.index'))->with('success', 'Project Users added successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong, ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // code here
    }

    public function edit($id)
    {
        $clientId = decrypt($id);
        $client = Client::find($clientId);
        if ($client) {
            return view('project.client.edit', compact('client'));
        }
        return redirect()->back()->with('error', 'Project Users not found,');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        try {
            $clientId = decrypt($id);
            $client = Client::findOrFail($clientId);
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'landmark' => $request->landmark,
                'city' => $request->city,
                'state' => $request->state,
                'pin_code' => $request->pin_code,
                'profile_image' => $request->profile_image,
                'description' => $request->description,
            ];
            if ($client->update($updateData)) {
                return redirect(route('admin.client.index'))->with('success', 'Project Users updated successfully');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $clientId = decrypt($id);
        $delete = Client::find($clientId);
        if ($delete) {
            $delete->delete();
            return redirect()->back()->with('success', 'Project Users deleted successfully');
        }
        return redirect()->back()->with('error', 'Project Users cannot delete');
    }
}
