<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $cycles=Cycle::all();
            return response()->json($cycles);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération de la liste des cycles");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $cycle=new Cycle([
                "name"=>$request->input("name"),
            ]);
            $cycle->save();


            return response()->json($cycle);

        } catch (\Exception $e) {
            return response()->json("insertion impossible");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $cycle=Cycle::findOrFail($id);
            return response()->json($cycle);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $cycle=Cycle::findorFail($id);
            $cycle->update($request->all());
            return response()->json($cycle);
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
            $cycle=Cycle::findOrFail($id);
            $cycle->delete();
            return response()->json("filiere supprimée avec succes");
        } catch (\Exception $e) {
            return response()->json("probleme de suppression de cycle");
        }
    }

}
