<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // Référence à l'id de la table User
        'admission_no',
    ];

    /**
     * Relation avec User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function students()
    {
        return $this->hasMany(Student::class , 'parent_id');
    }
}
