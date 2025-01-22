<?php

namespace App\Http\Controllers;

use App\Models\CourseFile;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CourseFileController extends Controller
{
    /**
     * Afficher tous les fichiers de cours.
     */
    public function index()
    {
        try {
            $courseFiles = CourseFile::with(['subject', 'teacher'])->get();
            return response()->json($courseFiles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Impossible de récupérer les fichiers de cours : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Ajouter un fichier de cours, uniquement autorisé pour les enseignants.
     */
    public function store(Request $request, $subjectId)
    {
        try {
            $user = Auth::user();

            // Vérification si l'utilisateur est un enseignant
            if (!Auth::user() || Auth::user()->role !== 'teacher') {
                return response()->json(['error' => 'Seuls les enseignants peuvent ajouter un fichier de cours.'], 403);
            }

            // Validation des données
            $validated = $request->validate([
                'file_name' => 'required|string|max:255', // Nom du fichier
                'file_path' => 'required|file|mimes:pdf,doc,docx|max:2048', // Fichier obligatoire
            ]);

            $teacherId = $user->teacher->id;

            // Vérification de l'existence du sujet
            $subject = Subject::find($subjectId);
            if (!$subject) {
                return response()->json(['error' => 'Sujet introuvable.'], 404);
            }
            // Enregistrement du fichier
            $filePath = $request->file('file_path')->storeAs(
                'course_files', // Dossier de stockage
                uniqid() . '_' . $request->file('file_path')->getClientOriginalName() // Nom unique du fichier
            );

            if (!$filePath) {
                return response()->json(['error' => 'Le fichier n\'a pas pu être téléchargé.'], 500);
            }

            // Création du fichier de cours
            $courseFile = new CourseFile([
                'subject_id' => $subjectId, // Associer au sujet
                'teacher_id' => $teacherId, // Associer à l'enseignant connecté
                'file_name' => $validated['file_name'], // Nom du fichier
                'file_path' => $filePath, // Chemin du fichier
            ]);

            // Sauvegarde du fichier de cours
            $courseFile->save();

            return response()->json([
                'message' => 'Fichier de cours ajouté avec succès.',
                'courseFile' => $courseFile,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Données invalides.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de l’ajout du fichier : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Télécharger un fichier de cours.
     */
    public function download($courseFileId)
    {
        try {
            $courseFile = CourseFile::find($courseFileId);
            if (!$courseFile) {
                return response()->json(['error' => 'Fichier de cours non trouvé.'], 404);
            }

            $filePath = $courseFile->file_path;
            if (!$filePath || !Storage::exists($filePath)) {
                return response()->json(['error' => 'Fichier non trouvé.'], 404);
            }

            return Storage::download($filePath, basename($filePath));
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors du téléchargement : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Afficher un fichier de cours spécifique.
     */
    public function show($id)
    {
        try {
            $courseFile = CourseFile::with(['subject', 'teacher'])->findOrFail($id);
            return response()->json($courseFile, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Fichier de cours introuvable : {$e->getMessage()}"], 404);
        }
    }

    /**
     * Mettre à jour un fichier de cours.
     */
    public function update(Request $request, $id)
    {
        try {
            $courseFile = CourseFile::findOrFail($id);
            $validated = $request->validate([
                'file_name' => 'required|string|max:255',
                'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // Fichier facultatif
            ]);

            if ($request->hasFile('file_path')) {
                // Supprimer l'ancien fichier s'il existe
                if ($courseFile->file_path && Storage::exists($courseFile->file_path)) {
                    Storage::delete($courseFile->file_path);
                }

                $courseFile->file_path = $request->file('file_path')->storeAs(
                    'course_files',
                    uniqid() . '_' . $request->file('file_path')->getClientOriginalName()
                );
            }

            $courseFile->update($validated);

            return response()->json(['message' => 'Fichier de cours mis à jour avec succès.', 'courseFile' => $courseFile], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Données invalides.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de la mise à jour : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Supprimer un fichier de cours.
     */
    public function destroy($id)
    {
        try {
            $courseFile = CourseFile::findOrFail($id);

            if ($courseFile->file_path && Storage::exists($courseFile->file_path)) {
                Storage::delete($courseFile->file_path);
            }

            $courseFile->delete();

            return response()->json(['message' => 'Fichier de cours supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de la suppression : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Afficher les fichiers de cours paginés.
     */
    public function courseFilesPaginate()
    {
        try {
            $perPage = request()->input('pageSize', 10);
            $courseFiles = CourseFile::with(['subject', 'teacher'])->paginate($perPage);

            return response()->json([
                'courseFiles' => $courseFiles->items(),
                'totalPages' => $courseFiles->lastPage(),
                'total' => $courseFiles->total(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de la pagination : {$e->getMessage()}"], 500);
        }
    }

    /**
     * Récupérer les fichiers d'un cours spécifique.
     */
    public function getCourseFilesBySubject($subjectId)
    {
        try {
            $subject = Subject::with('courseFiles')->findOrFail($subjectId);
            return response()->json($subject, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Erreur lors de la récupération des fichiers : {$e->getMessage()}"], 404);
        }
    }
}
