<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case CompanyAdmin = 'Company Admin';
    case Operator = 'Operator';
    case Viewer = 'Viewer';
}
