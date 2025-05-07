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
       // Admin Routes
       Route::post('register-admin', [AdminController::class, 'registerAdmin'])->name('admin.register');
       Route::get('admins', [AdminController::class, 'getAllAdmins'])->name('admin.all');
       Route::get('admins/{id}', [AdminController::class, 'getAdminById'])->name('admin.get');
       Route::put('admins/{id}', [AdminController::class, 'updateAdmin'])->name('admin.update');
       Route::delete('admins/{id}', [AdminController::class, 'deleteAdmin'])->name('admin.delete');
       
       // Teacher Routes
       Route::get('/teachers/total', [TeacherController::class, 'getTotalTeachers'])->name('teacher.total');
       Route::post('add-teacher', [TeacherController::class, 'addTeacher'])->name('teacher.add');
       Route::get('teachers', [TeacherController::class, 'getAllTeachers'])->name('teacher.all');
       Route::get('teachers/{id}', [TeacherController::class, 'getTeacherById'])->name('teacher.get');
       Route::put('teachers/{id}', [TeacherController::class, 'updateTeacher'])->name('teacher.update');
       Route::delete('teachers/{id}', [TeacherController::class, 'deleteTeacher'])->name('teacher.delete');
       Route::post('/teachers/{teacherId}/assign-subjects', [TeacherController::class, 'assignSubject']);

       // Parent Routes
       Route::post('add-parents', [ParentsController::class, 'addParents'])->name('parent.add');
        Route::get('parents', [ParentsController::class, 'getAllParents'])->name('parent.all');
        Route::get('parentsnp', [ParentsController::class, 'getAllParentsnp'])->name('parent.all');
        Route::get('parents/{id}', [ParentsController::class, 'getParentById'])->name('parent.get');
        Route::put('parents/{id}', [ParentsController::class, 'updateParent'])->name('parent.update');
        Route::delete('parents/{id}', [ParentsController::class, 'deleteParent'])->name('parent.delete');
   
        // StudentController Routes
        Route::post('add-student', [StudentController::class, 'addStudent'])->name('student.add');
        Route::get('students', [StudentController::class, 'getAll'])->name('student.all');
        Route::get('studentsPaginated', [StudentController::class, 'getAllPaginated'])->name('student.allPaginated');

        Route::get('student/{id}', [StudentController::class, 'getById'])->name('student.get');
        Route::delete('student/{id}', [StudentController::class, 'delete'])->name('student.delete');
        Route::put('student/{id}', [StudentController::class, 'update'])->name('student.update');    
        Route::get('/cycles/{cycleId}/students/total', [StudentController::class, 'getTotalStudentsByCycle']);
        Route::get('/fields/{fieldId}/students/total', [StudentController::class, 'getTotalStudentsByField']);
        Route::get('/specializations/{specializationId}/students/total', [StudentController::class, 'getTotalStudentsBySpecialization']);
        Route::get('/students/total', [StudentController::class, 'getTotalStudents']);   
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

