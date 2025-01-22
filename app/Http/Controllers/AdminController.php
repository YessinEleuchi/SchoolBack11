<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Create a new admin.
     */
    public function registerAdmin(Request $request)
    {
        // Vérifiez que l'utilisateur est authentifié et est un admin
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can register new admins.',
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
        ], [
            'email.unique' => 'The email address is already in use.',
            'password.min' => 'The password must be at least 6 characters.',
        ]);

        try {
            // Step 1: Create the user in the `users` table
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => RoleEnum::Admin->value, // Use RoleEnum for the role
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'dateofbirth' => $request->dateofbirth,
            ]);

            Log::info('User created:', $user->toArray());

            // Step 2: Use the `id` from the `users` table to create an entry in the `admins` table
            $admin = Admin::create([
                'user_id' => $user->id, // Correct foreign key
                'admission_no' => $request->admission_no,
            ]);

            Log::info('Admin created:', $admin->toArray());

            // Return a success response
            return response()->json([
                'message' => 'Admin created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating admin:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the admin.',
            ], 500);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to generate a token based on credentials
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            Log::warning('Login failed for email:', ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }

        // Retrieve authenticated user
        $user = Auth::user();

        Log::info('User logged in:', ['email' => $user->email]);

        // Return success response with token and user data
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }
}
