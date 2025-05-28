<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // Only allow access via 'role:Admin' middleware in routes!

    public function index()
    {
        return Skill::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|unique:skills,name']);
        $skill = Skill::create($data);
        return response()->json($skill, 201);
    }

    public function update(Request $request, Skill $skill)
    {
        $data = $request->validate(['name' => 'required|unique:skills,name,' . $skill->id]);
        $skill->update($data);
        return response()->json($skill);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted']);
    }
}