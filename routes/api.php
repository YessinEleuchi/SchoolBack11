<?php

use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\TeacherController;

// Route publique : Login
Route::post('login', [AdminController::class, 'login'])->name('admin.login');

// Routes protégées nécessitant une authentification
Route::middleware('auth:api')->group(function () {
    // Routes accessibles uniquement par l'admin
    Route::middleware('admin')->group(function () {
        // AdminController Routes
        Route::post('register-admin', [AdminController::class, 'registerAdmin'])->name('admin.register');
        // TeacherController Routes
        Route::post('add-teacher', [TeacherController::class, 'addTeacher'])->name('teacher.add');
        Route::post('/teachers/{teacherId}/assign-subjects', [TeacherController::class, 'assignSubject']);
        // ParentsController Routes
        Route::post('add-parent', [ParentsController::class, 'addParents'])->name('parent.add');
        // StudentController Routes
        Route::post('add-student', [StudentController::class, 'addStudent'])->name('student.add');
        // Ressources réservées à l'admin
        Route::resource('fields', FieldController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('cycles', CycleController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('groups', GroupController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('levels', LevelController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('subjects', SubjectController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('specializations', SpecializationController::class)->except(['index', 'show', 'create', 'edit']);
    });

    // Routes accessibles par tous les utilisateurs authentifiés
    Route::resource('fields', FieldController::class)->only(['index', 'show']);
    Route::resource('cycles', CycleController::class)->only(['index', 'show']);
    Route::resource('groups', GroupController::class)->only(['index', 'show']);
    Route::resource('levels', LevelController::class)->only(['index', 'show']);
    Route::resource('subjects', SubjectController::class)->only(['index', 'show']);
    Route::resource('specializations', SpecializationController::class)->only(['index', 'show']);
    Route::get('levels/section/{idSection}', [LevelController::class, 'showLevelsBySpecialization'])->name('levels.bySpecialization');
    Route::get('levels-paginate', [LevelController::class, 'LevelsPaginate'])->name('levels.paginate');
});
use App\Http\Controllers\CourseFileController;

Route::middleware('auth:api')->group(function () {
    // Afficher tous les fichiers de cours
    Route::get('course-files', [CourseFileController::class, 'index']);

    // Ajouter un fichier de cours
    Route::post('course-files/{subjectId}', [CourseFileController::class, 'store']);

    // Télécharger un fichier de cours
    Route::get('course-files/{courseFileId}/download', [CourseFileController::class, 'download']);

    // Afficher un fichier de cours spécifique
    Route::get('course-files/{id}', [CourseFileController::class, 'show']);

    // Mettre à jour un fichier de cours
    Route::put('course-files/{id}', [CourseFileController::class, 'update']);

    // Supprimer un fichier de cours
    Route::delete('course-files/{id}', [CourseFileController::class, 'destroy']);

    // Afficher les fichiers de cours paginés
    Route::get('course-files/paginate', [CourseFileController::class, 'courseFilesPaginate']);

    // Récupérer les fichiers d'un cours spécifique
    Route::get('courses/{courseId}/course-files', [CourseFileController::class, 'getCourseFilesByCourse']);
});

