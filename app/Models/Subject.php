<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{

    protected $fillable = ['name',  'level_id'];
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

}
