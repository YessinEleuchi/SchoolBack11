<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\TeacherStatutEnum;
use App\Models\CourseFile;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * Ajouter un enseignant.
     */
    public function addTeacher(Request $request)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (Auth::user()->role !== RoleEnum::Admin->value) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only admins can add teachers.',
            ], 403); // 403 Forbidden
        }

        // Validation de la requête
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'dateofbirth' => 'required|date',
            'admission_no' => 'required|string|unique:teachers', // Admission no unique
            'status' => 'required|string|in:' . implode(',', TeacherStatutEnum::values()), // Enum des statuts
        ]);

        // Étape 1 : Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => RoleEnum::Teacher->value, // Rôle enseignant
        ]);

        // Étape 2 : Créer l'enseignant
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'admission_no' => $request->admission_no,
            'status' => $request->status,
        ]);

        // Réponse de succès
        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher,
        ], 201);
    }

    /**
     * Assigner une matière à un enseignant.
     */
    public function assignSubject(Request $request, $teacherId)
    {
        // Validation de la requête
        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        // Récupérer l'enseignant
        $teacher = Teacher::findOrFail($teacherId);

        // Assigner les matières
        $teacher->subjects()->syncWithoutDetaching($request->subject_ids);

        return response()->json([
            'message' => 'Subjects assigned successfully.',
            'subjects' => $teacher->subjects,
        ]);
    }
    public function addCourseFile(Request $request, $subjectId)
    {
        // Vérifier que l'utilisateur est un enseignant
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Unauthorized: Only teachers can add course files.'], 403);
        }

        // Vérifier que le sujet appartient à cet enseignant
        $subject = $teacher->subjects()->where('subject_id', $subjectId)->first();

        if (!$subject) {
            return response()->json(['message' => 'This subject does not belong to you.'], 403);
        }

        // Valider la requête
        $request->validate([
            'file' => 'required|file|max:2048', // Limite de taille 2MB
        ]);

        // Enregistrer le fichier
        $file = $request->file('file');
        $filePath = $file->store('course_files', 'public'); // Stocker dans le dossier public/course_files
        $fileName = $file->getClientOriginalName();

        // Créer l'entrée dans la table course_files
        $courseFile = CourseFile::create([
            'subject_id' => $subjectId,
            'teacher_id' => $teacher->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Course file uploaded successfully',
            'course_file' => $courseFile,
        ], 201);
    }

}
