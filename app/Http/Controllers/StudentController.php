<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\StatutStudentEnum;
use App\Models\Student;
use App\Models\User;
use App\Models\Group;
use App\Models\Specialization;
use App\Models\Field;
use App\Models\Level;
use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 


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
 * Récupérer tous les étudiants avec pagination.
 */
public function getAllPaginated(Request $request)
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

            // Construire la requête pour récupérer les étudiants
            $query = Student::with(['user', 'group', 'parent.user']);

            // Appliquer le filtre de recherche si un terme est fourni
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                                 ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('parent.user', function ($parentUserQuery) use ($search) {
                        $parentUserQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            }

            // Paginer les résultats
            $students = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'students' => $students->items(),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                ],
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
  /**
     * Calculer le nombre d'étudiants par cycle, groupé par filière.
     */
    public function getStudentsByCycleAndField(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Récupérer les données des étudiants par cycle et filière
            $data = Student::select(
                'cycles.id as cycle_id',
                'cycles.name as cycle_name',
                'fields.name as field_name',
                DB::raw('count(students.id) as student_count')
            )
                ->join('groups', 'students.group_id', '=', 'groups.id')
                ->join('levels', 'groups.level_id', '=', 'levels.id')
                ->join('specializations', 'levels.specialization_id', '=', 'specializations.id')
                ->join('fields', 'specializations.field_id', '=', 'fields.id')
                ->join('cycles', 'fields.cycle_id', '=', 'cycles.id')
                ->groupBy('cycles.id', 'cycles.name', 'fields.name')
                ->orderBy('cycles.id')
                ->get();

            // Organiser les données pour le frontend
            $cycles = Cycle::all()->pluck('name', 'id')->toArray();
            $result = [];

            foreach ($cycles as $cycleId => $cycleName) {
                $result[$cycleId] = [
                    'cycle_name' => $cycleName,
                    'fields' => []
                ];
            }

            foreach ($data as $row) {
                $result[$row->cycle_id]['fields'][] = [
                    'field_name' => $row->field_name,
                    'student_count' => $row->student_count
                ];
            }

            // Convertir en tableau indexé et filtrer les cycles vides
            $result = array_values(array_filter($result, function ($cycle) {
                return !empty($cycle['fields']);
            }));

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
     * Récupérer les étudiants par groupe.
     */
    public function getStudentByGroup(Request $request, $groupId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Vérifier que le groupe existe
            $group = Group::find($groupId);
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found.',
                ], 404);
            }

            $perPage = $request->input('per_page', 10); // Par défaut 10 résultats par page

            // Récupérer les étudiants du groupe avec pagination
            $students = Student::where('group_id', $groupId)
                ->with(['user', 'group', 'parent.user'])
                ->paginate($perPage);

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
     * Récupérer les étudiants par spécialisation.
     */
    public function getStudentBySpecialization(Request $request, $specializationId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Vérifier que la spécialisation existe
            $specialization = Specialization::find($specializationId);
            if (!$specialization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not found.',
                ], 404);
            }

            $perPage = $request->input('per_page', 10); // Par défaut 10 résultats par page

            // Récupérer les étudiants via les groupes associés aux niveaux de la spécialisation
            $students = Student::whereHas('group.level.specialization', function ($query) use ($specializationId) {
                $query->where('id', $specializationId);
            })
                ->with(['user', 'group', 'parent.user'])
                ->paginate($perPage);

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
     * Récupérer les étudiants par filière.
     */
    public function getStudentByField(Request $request, $fieldId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Vérifier que la filière existe
            $field = Field::find($fieldId);
            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field not found.',
                ], 404);
            }

            $perPage = $request->input('per_page', 10); // Par défaut 10 résultats par page

            // Récupérer les étudiants via les groupes associés aux niveaux des spécialisations de la filière
            $students = Student::whereHas('group.level.specialization.field', function ($query) use ($fieldId) {
                $query->where('id', $fieldId);
            })
                ->with(['user', 'group', 'parent.user'])
                ->paginate($perPage);

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
     * Récupérer les étudiants par niveau.
     */
    public function getStudentByLevel(Request $request, $levelId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Vérifier que le niveau existe
            $level = Level::find($levelId);
            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => 'Level not found.',
                ], 404);
            }

            $perPage = $request->input('per_page', 10); // Par défaut 10 résultats par page

            // Récupérer les étudiants via les groupes associés au niveau
            $students = Student::whereHas('group.level', function ($query) use ($levelId) {
                $query->where('id', $levelId);
            })
                ->with(['user', 'group', 'parent.user'])
                ->paginate($perPage);

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
     * Récupérer les étudiants par cycle.
     */
    public function getStudentByCycle(Request $request, $cycleId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please authenticate.',
                ], 401);
            }

            // Vérifier que le cycle existe
            $cycle = Cycle::find($cycleId);
            if (!$cycle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cycle not found.',
                ], 404);
            }

            $perPage = $request->input('per_page', 10); // Par défaut 10 résultats par page

            // Récupérer les étudiants via les groupes associés aux niveaux des spécialisations des filières du cycle
            $students = Student::whereHas('group.level.specialization.field.cycle', function ($query) use ($cycleId) {
                $query->where('id', $cycleId);
            })
                ->with(['user', 'group', 'parent.user'])
                ->paginate($perPage);

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
 * Calculer le nombre total d'étudiants par cycle.
 */
public function getTotalStudentsByCycle(Request $request, $cycleId)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Please authenticate.',
            ], 401);
        }

        // Vérifier que le cycle existe
        $cycle = Cycle::find($cycleId);
        if (!$cycle) {
            return response()->json([
                'success' => false,
                'message' => 'Cycle not found.',
            ], 404);
        }

        // Compter les étudiants via les groupes associés aux niveaux des spécialisations des filières du cycle
        $totalStudents = Student::whereHas('group.level.specialization.field.cycle', function ($query) use ($cycleId) {
            $query->where('id', $cycleId);
        })->count();

        return response()->json([
            'success' => true,
            'total_students' => $totalStudents,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Calculer le nombre total d'étudiants par filière.
 */
public function getTotalStudentsByField(Request $request, $fieldId)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Please authenticate.',
            ], 401);
        }

        // Vérifier que la filière existe
        $field = Field::find($fieldId);
        if (!$field) {
            return response()->json([
                'success' => false,
                'message' => 'Field not found.',
            ], 404);
        }

        // Compter les étudiants via les groupes associés aux niveaux des spécialisations de la filière
        $totalStudents = Student::whereHas('group.level.specialization.field', function ($query) use ($fieldId) {
            $query->where('id', $fieldId);
        })->count();

        return response()->json([
            'success' => true,
            'total_students' => $totalStudents,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Calculer le nombre total d'étudiants par spécialisation.
 */
public function getTotalStudentsBySpecialization(Request $request, $specializationId)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Please authenticate.',
            ], 401);
        }

        // Vérifier que la spécialisation existe
        $specialization = Specialization::find($specializationId);
        if (!$specialization) {
            return response()->json([
                'success' => false,
                'message' => 'Specialization not found.',
            ], 404);
        }

        // Compter les étudiants via les groupes associés aux niveaux de la spécialisation
        $totalStudents = Student::whereHas('group.level.specialization', function ($query) use ($specializationId) {
            $query->where('id', $specializationId);
        })->count();

        return response()->json([
            'success' => true,
            'total_students' => $totalStudents,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Calculer le nombre total d'étudiants (tous cycles, toutes filières, toutes spécialisations).
 */
public function getTotalStudents(Request $request)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Please authenticate.',
            ], 401);
        }

        // Compter tous les étudiants
        $totalStudents = Student::count();

        return response()->json([
            'success' => true,
            'total_students' => $totalStudents,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

}
