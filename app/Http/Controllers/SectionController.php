<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
        ]);

        $section = Section::create($data);

        return redirect()->back()->with('success', 'Section created.');
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update($data);

        return redirect()->back()->with('success', 'Section updated.');
    }
}
