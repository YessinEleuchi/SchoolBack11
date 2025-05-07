<?php
namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\TeacherStatutEnum;
use App\Models\Teacher;
use App\Models\User;
use App\Models\CourseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function addTeacher(Request $request)
    {
        try {
            if (Auth::user()->role !== RoleEnum::Admin->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only admins can add teachers.',
                ], 403);
            }
    
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'gender' => 'required|string',
                'phone' => 'required|string',
                'address' => 'required|string',
                'dateofbirth' => 'required|date',
                'admission_no' => 'required|string|unique:teachers',
                'status' => 'required|string|in:' . implode(',', TeacherStatutEnum::values()),
            ]);
    
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'dateofbirth' => $validated['dateofbirth'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'password' => Hash::make($validated['password']),
                'role' => RoleEnum::Teacher->value,
            ]);
    
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'admission_no' => $validated['admission_no'],
                'status' => $validated['status'],
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'teacher' => $teacher,
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllTeachers(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Définir le nombre d'éléments par page (par défaut 6)
            $perPage = $request->input('per_page', 6);
            $search = $request->input('search', '');

            // Construire la requête pour récupérer les enseignants
            $query = Teacher::with('user');

            // Appliquer le filtre de recherche si un terme est fourni
            if (!empty($search)) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Paginer les résultats
            $teachers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'teachers' => $teachers->items(),
                'pagination' => [
                    'current_page' => $teachers->currentPage(),
                    'last_page' => $teachers->lastPage(),
                    'per_page' => $teachers->perPage(),
                    'total' => $teachers->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTeacherById($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        return response()->json(['data' => $teacher], 200);
    }

    public function updateTeacher(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = $teacher->user;

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:6',
            'gender' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'dateofbirth' => 'sometimes|date',
            'admission_no' => 'sometimes|string|unique:teachers,admission_no,' . $teacher->id,
            'status' => 'sometimes|string|in:' . implode(',', TeacherStatutEnum::values()),
        ]);

        try {
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

            $teacherData = [];
            if ($request->has('admission_no')) $teacherData['admission_no'] = $request->admission_no;
            if ($request->has('status')) $teacherData['status'] = $request->status;

            if (!empty($teacherData)) {
                $teacher->update($teacherData);
            }

            return response()->json([
                'message' => 'Teacher updated successfully',
                'teacher' => $teacher,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the teacher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = $teacher->user;

        try {
            $teacher->delete();
            $user->delete();
            return response()->json([
                'message' => 'Teacher deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the teacher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


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
    public function getTotalTeachers(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }
    
            // Count all teachers
            $totalTeachers = Teacher::count();
    
            return response()->json([
                'success' => true,
                'total_teachers' => $totalTeachers, // Fixed key name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}