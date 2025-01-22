<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $fillable = ['name', 'field_id'];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }
}
