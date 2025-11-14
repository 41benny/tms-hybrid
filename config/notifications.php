<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Finance Team Roles
    |--------------------------------------------------------------------------
    |
    | List of user roles that should receive payment request notifications.
    | You can customize this list based on your application's role structure.
    |
    */

    'finance_team_roles' => [
        'super_admin',
        'admin',
        'sales',
    ],

    /*
    |--------------------------------------------------------------------------
    | Finance Team User IDs
    |--------------------------------------------------------------------------
    |
    | Specific user IDs that should always receive payment request notifications.
    | This is useful if you want to notify specific users regardless of their role.
    |
    */

    'finance_team_user_ids' => [
        // Add specific user IDs here if needed
        // Example: 1, 2, 3
    ],

];
