<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::all();
        return view('features.index', compact('features'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:features,name',
        ]);

        Feature::create($data);

        return redirect()->route('features.index')->with('success', 'Feature added.');
    }

    public function update(Request $request, Feature $feature)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:features,name,' . $feature->id,
        ]);

        $feature->update($data);

        return redirect()->route('features.index')->with('success', 'Feature updated.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();
        return redirect()->route('features.index')->with('success', 'Feature removed.');
    }
}
