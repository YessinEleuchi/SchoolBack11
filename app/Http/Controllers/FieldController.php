<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Group;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $fields=Field::class::with('cycle')->get();
            return response()->json($fields,200);
        } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $field=new Field([
                "name"=> $request->input('name'),
                "cycle_id"=> $request->input('cycle_id'),
            ]);
            $field->save();
            return response()->json($field);
        } catch (\Exception $e) {
            return response()->json("insertion impossible {$e->getMessage()}");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $field=Field::findOrFail($id);
            return response()->json($field);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données {$e->getMessage()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $field=Field::findorFail($id);
            $field->update($request->all());
            return response()->json($field);
        } catch (\Exception $e) {
            return response()->json("probleme de modification $e-> {getMessage()},{getCode()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $field=Field::findOrFail($id);
            $field->delete();
            return response()->json("filiere supprimée avec succes");
        } catch (\Exception $e) {
            return response()->json("probleme de suppression de filiere");
        }
    }
}
