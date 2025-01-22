<?php

namespace App\Models;

use App\Enums\TeacherStatutEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // Référence à l'id de la table User
        'class_id', // Champ spécifique aux enseignants
        'admission_no',
        'status',
    ];

    /**
     * Relation avec User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Cast status to TeacherStatutEnum
    protected $casts = [
        'status' => TeacherStatutEnum::class,
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }


    /**
     * Relation entre l'enseignant et les fichiers de cours.
     */
    public function courseFiles()
    {
        return $this->hasMany(CourseFile::class);
    }


}
