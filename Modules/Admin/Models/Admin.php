<?php

namespace Modules\Admin\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles;

    protected $guard_name = 'admin'; // Important!
}
