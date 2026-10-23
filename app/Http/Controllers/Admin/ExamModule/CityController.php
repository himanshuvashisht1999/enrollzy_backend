<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('state', 'like', "%$s%"))
            ->orderBy('name')->paginate(30)->withQueryString();
        $usage = \Illuminate\Support\Facades\DB::table('exm_centres')->selectRaw('city_id, count(*) c')->groupBy('city_id')->pluck('c', 'city_id');
        return view('admin.exam-module.cities.index', compact('cities', 'usage'));
    }

    public function store(Request $request)
    {
        $city = City::create($this->validated($request));

        // Quick-add from the exam centre form.
        if ($request->expectsJson()) {
            return response()->json(['id' => $city->id, 'label' => $city->name . ($city->state ? ', ' . $city->state : '')], 201);
        }
        return back()->with('success', 'City added.');
    }

    public function update(Request $request, City $city)
    {
        $city->update($this->validated($request, $city) + ['status' => $request->boolean('status')]);
        return back()->with('success', 'City updated.');
    }

    public function destroy(City $city)
    {
        if (\Illuminate\Support\Facades\DB::table('exm_centres')->where('city_id', $city->id)->exists()) {
            return back()->with('error', 'This city is used by exam centres. Mark it inactive instead.');
        }
        $city->delete();
        return back()->with('success', 'City deleted.');
    }

    private function validated(Request $request, ?City $city = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'state' => ['nullable', 'string', 'max:100', Rule::unique('exm_cities', 'state')->where('name', $request->name)->ignore($city?->id)],
        ], ['state.unique' => 'This city already exists for that state.']);
        // The unique index ignores NULL states, so check name + state explicitly.
        $exists = City::where('name', $data['name'])->where('state', $data['state'] ?? null)
            ->when($city, fn ($q) => $q->where('id', '!=', $city->id))->exists();
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'This city already exists for that state.']);
        }
        $data['status'] = true;
        return $data;
    }
}
