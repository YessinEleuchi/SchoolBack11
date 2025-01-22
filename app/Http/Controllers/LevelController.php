<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $levels=Level::with('specialization')->get(); // Inclut la sous catégorie liée;
            return response()->json($levels,200);
        } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
        }    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $Level=new Level([
                "name"=> $request->input('name'),
                "specialization_id"=> $request->input('specialization_id'),
            ]);
            $Level->save();
            return response()->json($Level);

        } catch (\Exception $e) {
            return response()->json("insertion impossible {$e->getMessage()}");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $Level=Level::findOrFail($id);
            return response()->json($Level);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données {$e->getMessage()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        try {
            $Level=Level::findorFail($id);
            $Level->update($request->all());
            return response()->json($Level);
        } catch (\Exception $e) {
            return response()->json("probleme de modification {$e->getMessage()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        try {
            $Level=Level::findOrFail($id);
            $Level->delete();
            return response()->json("Level supprimé avec succes");
        }
        catch (\Exception $e) {
            return response()->json("probleme de suppression de ce level {$e->getMessage()}");
        }
    }
    public function showLevelsBySpecialization($idspecialization)
    {
        try {
            $Levels= Level::where('specialization_id', $idspecialization)->with('specialization')->get();
            return response()->json($Levels);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }
    public function LevelsPaginate()
    {
        try {
            // Set the perPage dynamically or fallback to 2 as the default
            $perPage = request()->input('pageSize', 10);

            // Paginate the Levels
            $Levels = Level::with('specialization')->paginate($perPage);

            // Return the paginated results
            return response()->json([
                'Levels' => $Levels->items(),    // Paginated items
                'totalPages' => $Levels->lastPage(), // Total pages
                'currentPage' => $Levels->currentPage(), // Current page
            ]);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }
}
