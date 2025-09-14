<?php

namespace App\Models;

use Modules\Fresnel\app\Models\User as FresnelUser;

/**
 * App User Model - extends Fresnel User
 * 
 * This allows us to use the rich Fresnel User model throughout the application
 * while maintaining Laravel's default User model location for compatibility.
 */
class User extends FresnelUser
{
    // Inherits all functionality from Fresnel User model
    // Additional global user functionality can be added here if needed
}
