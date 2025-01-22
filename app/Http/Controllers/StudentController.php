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
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'admission_no' => 'required|string|unique:students,admission_no',
                'gender' => 'required|string|in:male,female',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'dateofbirth' => 'required|date',
                'status' => 'required|string|in:' . implode(',', StatutStudentEnum::values()), // Enum des statuts
                'group_id' => 'required|exists:groups,id',
                'parent_id' => 'nullable|exists:parents,id',
            ]);

            // Créer l'utilisateur dans la table `users`
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => RoleEnum::Student->value, // Rôle étudiant
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'dateofbirth' => $request->dateofbirth,
            ]);

            // Vérification de la création de l'utilisateur
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating user.',
                ], 500); // 500 Internal Server Error
            }

            // Créer l'étudiant avec les données de l'utilisateur
            $student = Student::create([
                'user_id' => $user->id,
                'admission_no' => $request->admission_no,
                'status' => $request->status,
                'group_id' => $request->group_id,
                'parent_id' => $request->parent_id,
            ]);

            // Vérification de la création de l'étudiant
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating student.',
                ], 500); // 500 Internal Server Error
            }

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Student created successfully',
                'student' => $student,
            ], 201); // 201 Created
        } catch (\Exception $e) {
            // Retourner une réponse avec l'erreur
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
}
