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
    // AdminController Routes
    Route::post('register-admin', [AdminController::class, 'registerAdmin'])->name('admin.register');
    // TeacherController Routes
    Route::post('add-teacher', [TeacherController::class, 'addTeacher'])->name('teacher.add');
    // ParentsController Routes
    Route::post('add-parent', [ParentsController::class, 'addParents'])->name('parent.add');
    // StudentController Routes
    Route::post('add-student', [StudentController::class, 'addStudent'])->name('student.add');
    // SectionController Routes (resource)
    Route::resource('fields', FieldController::class)->except(['create', 'edit']);
    // ClassesController Routes (resource)
    Route::resource('cycles', CycleController::class)->except(['create', 'edit']);
    Route::resource('groups', GroupController::class)->except(['create', 'edit']);
    // Routes RESTful pour LevelController
    Route::resource('levels', LevelController::class)->except(['create', 'edit']);
    // Route pour afficher les niveaux par section
    Route::get('levels/section/{idSection}', [LevelController::class, 'showLevelsBySpecialization'])->name('levels.bySpecialization');
    // Route pour la pagination des niveaux
    Route::get('levels-paginate', [LevelController::class, 'LevelsPaginate'])->name('levels.paginate');
    Route::resource('subjects', SubjectController::class)->except(['create', 'edit']);
    Route::post('/teachers/{teacherId}/assign-subjects', [TeacherController::class, 'assignSubject']);
    Route::resource('specializations', SpecializationController::class)->except(['create', 'edit']);
});
