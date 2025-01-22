<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['name', 'specialization_id'];

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
