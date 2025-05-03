<?php

namespace App\Models;

use App\Enums\StatutStudentEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admission_no',
        'group_id',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'status' => StatutStudentEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');  
    }
}
