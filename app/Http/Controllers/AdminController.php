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
    public function registerAdmin(Request $request)
    {
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can register new admins.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'admission_no' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
        ], [
            'email.unique' => 'The email address is already in use.',
            'password.min' => 'The password must be at least 6 characters.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => RoleEnum::Admin->value,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
            ]);

            $admin = Admin::create([
                'user_id' => $user->id,
                'admission_no' => $request->admission_no,
            ]);

            return response()->json([
                'message' => 'Admin created successfully',
                'user' => $user,
                'admin' => $admin,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating admin:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the admin.',
            ], 500);
        }
    }

    public function getAllAdmins()
    {
        $admins = Admin::with('user')->get();
        return response()->json(['data' => $admins], 200);
    }

    public function getAdminById($id)
    {
        $admin = Admin::with('user')->findOrFail($id);
        return response()->json(['data' => $admin], 200);
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'admission_no' => 'sometimes|string',
            'gender' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
        ]);

        try {
            $userData = [];
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('password')) $userData['password'] = Hash::make($request->password);
            if ($request->has('gender')) $userData['gender'] = $request->gender;
            if ($request->has('phone')) $userData['phone'] = $request->phone;
            if ($request->has('address')) $userData['address'] = $request->address;
            if ($request->has('date_of_birth')) $userData['date_of_birth'] = $request->date_of_birth;

            if (!empty($userData)) {
                $user->update($userData);
            }

            $adminData = [];
            if ($request->has('admission_no')) $adminData['admission_no'] = $request->admission_no;

            if (!empty($adminData)) {
                $admin->update($adminData);
            }

            return response()->json([
                'message' => 'Admin updated successfully',
                'user' => $user,
                'admin' => $admin,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating admin:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the admin.',
            ], 500);
        }
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;

        try {
            $admin->delete();
            $user->delete();
            return response()->json([
                'message' => 'Admin deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting admin:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the admin.',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            Log::warning('Login failed for email:', ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }
}