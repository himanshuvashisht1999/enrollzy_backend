<?php

namespace App\Http\Controllers\Backend\General;

use App\Models\Flash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Marque;
use Illuminate\Support\Facades\Validator;

class MarqueeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:slide-banner-list', ['only' => ['store']]);
        // $this->middleware('permission:slide-banner-create', ['only' => ['store']]);
        // $this->middleware('permission:slide-banner-edit', ['only' => ['update']]);
        // $this->middleware('permission:slide-banner-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $marque = Marque::get();
        return view('general.marquee.index', compact('marque'));
    }

    public function create()
    {
        return view('general.marquee.create');
    }

    public function store(Request $request)
    {
        $validator = Validator($request->all(), [
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $insertData = [
            'content' => $request->content,
        ];
        $result = Marque::create($insertData);
        staffLog('marquee', $result->id, 'create', ' Marquee created');
        if ($result) {
            return redirect(route('admin.marque.index'))->with('success', 'Marquee Created successfully');
        }
        return redirect()->back()->with('error', 'something went wrong')->withInput();
    }

    public function show()
    {
        // code here
    }

    public function edit($id)
    {
        $mId = decrypt($id);
        $marque = Marque::find($mId);
        if ($marque) {
            return view('general.marquee.edit', compact('marque'));
        }
        return redirect()->back()->with('error', 'Marque Slide Not found, Please refresh the page');
    }

    public function update(Request $request, $id)
    {
        $mId = decrypt($id);
        $marque = Marque::find($mId);
        if (!$marque) {
            return redirect()->back()->with('error', 'marque slide not found, please refresh the page.');
        }
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $marqueData = [
            'content' => $request->content,
        ];
        $result = $marque->update($marqueData);
        staffLog('marque', $marque->id, 'update', ' marque updated');
        if ($result) {
            return redirect(route('admin.marque.index'))->with('success', 'marque updated successfully');
        }
        return redirect()->back()->with('error', 'marque update Failed')->withInput();
    }

    public function destroy($id)
    {
        $mId = decrypt($id);
        $marque = Marque::find($mId);
        if ($marque) {
            staffLog('marque', $mId, 'delete', 'marque deleted');
            $marque->delete();
            return redirect()->back()->with('success', 'marque deleted successfully');
        }
        return redirect()->back()->with('error', 'something went wrong, please contact developer');
    }
}
