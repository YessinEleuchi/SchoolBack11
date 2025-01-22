<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Group;
use App\Models\Level;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $groups=Group::class::with('level')->get();
            return response()->json($groups,200);
        } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
        }    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $group=new Group([
                "name"=> $request->input('name'),
                "level_id"=> $request->input('level_id'),
            ]);
            $group->save();
            return response()->json($group);

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
            $classe=Group::findOrFail($id);
            return response()->json($classe);
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
            $group=Group::findorFail($id);
            $group->update($request->all());
            return response()->json($group);
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
            $group=Group::findOrFail($id);
            $group->delete();
            return response()->json("classe supprimé avec succes");
        }
        catch (\Exception $e) {
            return response()->json("probleme de suppression de ce classe {$e->getMessage()}");
        }
    }
    public function showclassesByLevel($idLevel)
    {
        try {
            $groups= Group::where('section_id', $idLevel)->with('section')->get();
            return response()->json($groups);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }
    public function classesPaginate()
    {
        try {
            // Set the perPage dynamically or fallback to 2 as the default
            $perPage = request()->input('pageSize', 10);

            // Paginate the classes
            $groups = Group::with('section')->paginate($perPage);

            // Return the paginated results
            return response()->json([
                'groups' => $groups->items(),    // Paginated items
                'totalPages' => $groups->lastPage(), // Total pages
                'currentPage' => $groups->currentPage(), // Current page
            ]);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }
}
