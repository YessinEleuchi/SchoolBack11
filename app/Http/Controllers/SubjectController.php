<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subjects = Subject::all();
            return response()->json($subjects);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème lors de la récupération des matières'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:subjects',
                'level_id' => 'required|exists:levels,id',
            ]);

            $subject = new Subject([
                'name' => $request->input('name'),
                'level_id' => $request->input('level_id'),
            ]);
            $subject->save();

            return response()->json($subject);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Insertion impossible', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            return response()->json($subject);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème lors de la récupération de la matière'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255|unique:subjects,name,' . $id,
                'level_id' => 'sometimes|exists:levels,id',
            ]);

            $subject = Subject::findOrFail($id);
            $subject->update($request->all());

            return response()->json($subject);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de modification', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return response()->json(['message' => 'Matière supprimée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème de suppression de la matière'], 500);
        }
    }

    /**
     * Get all teachers for a specific subject.
     */
    public function getTeachers(string $id)
    {
        try {
            $subject = Subject::with('teachers')->findOrFail($id);
            return response()->json($subject->teachers);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Problème lors de la récupération des enseignants'], 404);
        }
    }
}
