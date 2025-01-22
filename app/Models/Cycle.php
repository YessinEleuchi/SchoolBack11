<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    protected $fillable = ['name'];

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

}
