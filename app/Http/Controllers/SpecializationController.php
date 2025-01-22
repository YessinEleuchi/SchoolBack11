<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Level;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $Specializations=Specialization::class::with('field')->get();
            return response()->json($Specializations,200);
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
            $Specialization=new Specialization([
                "name"=> $request->input('name'),
                "field_id"=> $request->input('field_id'),
            ]);
            $Specialization->save();
            return response()->json($Specialization);

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
            $Specialization=Specialization::findOrFail($id);
            return response()->json($Specialization);
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
            $specialization=Specialization::findorFail($id);
            $specialization->update($request->all());
            return response()->json($specialization);
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
            $Specialization=Specialization::findOrFail($id);
            $Specialization->delete();
            return response()->json("filiere supprimée avec succes");
        } catch (\Exception $e) {
            return response()->json("probleme de suppression de filiere");
        }
    }
}
