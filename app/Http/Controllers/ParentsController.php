<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; // Importez JWTAuth
use Illuminate\Support\Facades\Auth;

class ParentsController extends Controller
{
    public function addParents(Request $request)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can add parents.',
            ], 403); // 403 Forbidden
        }
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'admission_no' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'dateofbirth' => 'nullable|date',
        ]);

        try {
            // Step 1: Create the user in the `users` table
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => RoleEnum::Parent->value,
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'date_of_birth' => $validated['dateofbirth'],
            ]);
            // Step 2: Use the `id` from the `users` table to create an entry in the `parents` table
            $parent = Parents::create([
                'user_id' => $user->id, // Correct foreign key
                'admission_no' => $request->admission_no,
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Parent created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the parent.',
            ], 500);
        }
    }

    /**
     * Get all parents.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllParents(Request $request)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can view parents.',
            ], 403);
        }

        try {
            // Récupérer les paramètres de pagination et de recherche
            $perPage = $request->query('per_page', 6); // Default to 6 parents per page
            $search = $request->query('search', '');

            // Construire la requête pour récupérer les parents
            $query = Parents::with('user');

            // Appliquer le filtre de recherche si un terme est fourni
            if (!empty($search)) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%')
                             ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }

            // Paginer les résultats
            $parents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Parents retrieved successfully',
                'parents' => $parents->items(),
                'pagination' => [
                    'current_page' => $parents->currentPage(),
                    'last_page' => $parents->lastPage(),
                    'per_page' => $parents->perPage(),
                    'total' => $parents->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving parents: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getAllParentsnp()
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can view parents.',
            ], 403);
        }

        try {
            // Retrieve all parents with their associated user data
            $parents = Parents::with('user')->get();
            return response()->json([
                'success' => true,
                'message' => 'Parents retrieved successfully',
                'parents' => $parents,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving parents.',
            ], 500);
        }
    }

    /**
     * Get a parent by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentById($id)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can view parents.',
            ], 403);
        }

        try {
            // Find the parent with associated user data
            $parent = Parents::with('user')->find($id);
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Parent retrieved successfully',
                'parent' => $parent,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the parent.',
            ], 500);
        }
    }

    /**
     * Update a parent.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
   
    public function updateParent(Request $request, $id)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can update parents.',
            ], 403);
        }

        // Validate the incoming request
        $parent = Parents::find($id);
        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $parent->user_id, // Use user_id
            'password' => 'sometimes|string|min:6',
            'admission_no' => 'sometimes|string',
            'gender' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'dateofbirth' => 'sometimes|date',
        ]);

        try {
            // Find the associated user
            $user = User::find($parent->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.',
                ], 404);
            }

            // Update user data if provided
            $userData = [];
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('password')) $userData['password'] = Hash::make($request->password);
            if ($request->has('gender')) $userData['gender'] = $request->gender;
            if ($request->has('phone')) $userData['phone'] = $request->phone;
            if ($request->has('address')) $userData['address'] = $request->address;
            if ($request->has('dateofbirth')) $userData['date_of_birth'] = $request->dateofbirth;
            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update parent data if provided
            $parentData = [];
            if ($request->has('admission_no')) $parentData['admission_no'] = $request->admission_no;
            if (!empty($parentData)) {
                $parent->update($parentData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Parent updated successfully',
                'user' => $user,
                'parent' => $parent,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the parent.',
            ], 500);
        }
    }


    /**
     * Delete a parent.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteParent($id)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can delete parents.',
            ], 403);
        }

        try {
            // Find the parent
            $parent = Parents::find($id);
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent not found.',
                ], 404);
            }

            // Find the associated user
            $user = User::find($parent->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Associated user not found.',
                ], 404);
            }

            // Delete both parent and user
            $parent->delete();
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parent deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the parent.',
            ], 500);
        }
    }
}