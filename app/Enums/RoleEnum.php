<?php


namespace App\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Student = 'Student';
    case Parent = 'Parent';
    case Teacher = 'Teacher';

}
