<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\StatutStudentEnum;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Ajouter un étudiant.
     */
    public function addStudent(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est authentifié et qu'il est un administrateur
            if (Auth::user()->role !== RoleEnum::Admin->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only admins can add students.',
                ], 403); // 403 Forbidden
            }

            // Validation des données
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'admission_no' => 'required|string|unique:students,admission_no',
                'gender' => 'required|string|in:male,female',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'dateofbirth' => 'required|date',
                'status' => 'required|string|in:' . implode(',', StatutStudentEnum::values()),
                'group_id' => 'required|exists:groups,id',
                'parent_id' => 'nullable|exists:parents,id',
            ]);

            // Créer l'utilisateur dans la table `users`
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => RoleEnum::Student->value,
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'date_of_birth' => $validated['dateofbirth'],
            ]);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating user.',
                ], 500);
            }

            // Créer l'étudiant
            $student = Student::create([
                'user_id' => $user->id,
                'admission_no' => $request->admission_no,
                'status' => $request->status,
                'group_id' => $request->group_id,
                'parent_id' => $request->parent_id,
            ]);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating student.',
                ], 500);
            }

            return response()->json([
                'message' => 'Student created successfully',
                'student' => $student,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer tous les étudiants.
     */
    public function getAll()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }
    
            // Load the 'user', 'group', and 'parent' relationships, and also load 'parent.user'
            $students = Student::with(['user', 'group', 'parent.user'])->get();
    
            return response()->json([
                'success' => true,
                'students' => $students,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un étudiant par ID.
     */
    public function getById($id)
    {
        try {
            // Vérifier que l'utilisateur est authentifié
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Récupérer l'étudiant avec ses informations d'utilisateur
            $student = Student::with('user')->find($id);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'student' => $student,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un étudiant.
     */
 /**
 * Supprimer un étudiant.
 */
public function delete($id)
{
    try {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Please authenticate.',
            ], 401);
        }

        // Vérifier que l'utilisateur est un administrateur
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can delete students.',
            ], 403);
        }

        // Trouver l'étudiant
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        // Trouver l'utilisateur associé
        $user = $student->user;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Associated user not found.',
            ], 404);
        }

        // Supprimer l'étudiant
        $student->delete();

        // Supprimer l'utilisateur associé
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student and associated user deleted successfully.',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Mettre à jour un étudiant.
     */
    public function update(Request $request, $id)
{
    try {
        // Vérifier que l'utilisateur est authentifié et administrateur
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can update students.',
            ], 403);
        }

        // Trouver l'étudiant
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        // Trouver l'utilisateur associé
        $user = User::find($student->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Associated user not found.',
            ], 404);
        }

        // Validation des données, excluant l'utilisateur actuel pour la vérification d'unicité de l'email
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'admission_no' => 'sometimes|string|unique:students,admission_no,' . $id,
            'gender' => 'sometimes|string|in:male,female',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'status' => 'sometimes|string|in:' . implode(',', StatutStudentEnum::values()),
            'group_id' => 'sometimes|exists:groups,id',
            'parent_id' => 'nullable|exists:parents,id',
        ]);

        // Mettre à jour les informations de l'utilisateur
        $userData = [
            'name' => $request->input('name', $user->name),
            'email' => $request->input('email', $user->email),
            'gender' => $request->input('gender', $user->gender),
            'phone' => $request->input('phone', $user->phone),
            'address' => $request->input('address', $user->address),
            'date_of_birth' => $request->input('date_of_birth', $user->date_of_birth),
        ];

        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Mettre à jour les informations de l'étudiant
        $studentData = [
            'admission_no' => $request->input('admission_no', $student->admission_no),
            'status' => $request->input('status', $student->status),
            'group_id' => $request->input('group_id', $student->group_id),
            'parent_id' => $request->input('parent_id', $student->parent_id),
        ];

        $student->update($studentData);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'student' => $student->load(['user', 'group', 'parent']),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}
}