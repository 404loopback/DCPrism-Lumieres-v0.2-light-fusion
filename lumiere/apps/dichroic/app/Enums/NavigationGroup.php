<?php

namespace App\Enums;

enum NavigationGroup: string
{
    case TECHNICIAN_TOOLS = 'Technician Tools';
    case INFRASTRUCTURE_MANAGEMENT = 'Infrastructure Management';
    case USER_MANAGEMENT = 'User Management';
    case MONITORING = 'Monitoring';
    case SETTINGS = 'Settings';
}
