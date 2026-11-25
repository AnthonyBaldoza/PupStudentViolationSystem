<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration codes
    |--------------------------------------------------------------------------
    | Each account type has its own code. Whoever registers must enter the code
    | that matches the account type they pick.
    |
    |   REGISTRATION_CODE        -> Student Council Officer accounts
    |   ADMIN_REGISTRATION_CODE  -> Admin (Teacher / Adviser) accounts
    |
    | If REGISTRATION_CODE is empty, registration is disabled completely
    | (/register returns 404). If ADMIN_REGISTRATION_CODE is empty, or is the
    | same as the officer code, the Admin option is not offered.
    |
    | Keep the admin code secret: anyone who has it can create an Admin account.
    */

    'code' => env('REGISTRATION_CODE'),

    'admin_code' => env('ADMIN_REGISTRATION_CODE'),

];