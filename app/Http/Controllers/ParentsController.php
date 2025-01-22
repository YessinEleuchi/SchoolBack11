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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'admission_no' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'dateofbirth' => 'required|date',
        ]);

        \Log::info('Validation passed:', $request->all());

        try {
            // Step 1: Create the user in the `users` table
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => RoleEnum::Parent->value, // Use RoleEnum for the role
                'admission_no' => $request->admission_no, // Renamed for consistency
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'dateofbirth' => $request->dateofbirth,
            ]);

            \Log::info('User created:', $user->toArray());

            // Step 2: Use the `id` from the `users` table to create an entry in the `parents` table
            $parent = Parents::create([
                'user_id' => $user->id, // Correct foreign key
                'admission_no' => $request->admission_no,
            ]);

            \Log::info('Parent created:', $parent->toArray());

            // Return a success response
            return response()->json([
                'message' => 'Parent created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating parent:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the parent.',
            ], 500);
        }
    }
}
