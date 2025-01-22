<?php

namespace App\Models;

use App\Enums\RoleEnum; // Import the RoleEnum
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'role',
        'gender',
        'date_of_birth',
        'phone',
        'address',
    ];

    /**
     * Cast the `role` attribute to an enum.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function Admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    public function parent()
    {
        return $this->hasOne(Parents::class, 'user_id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }


    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }



    /**
     * Get the identifier for the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Return the primary key of the user
    }

    /**
     * Get custom claims for the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->role,  // Suppose you have a 'role' column in the 'users' table
        ]; // You can add custom claims here if needed
    }
}
