<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registration code
    |--------------------------------------------------------------------------
    | Student Council officers must enter this code to create an account.
    | Set REGISTRATION_CODE in .env. If it is empty, registration is disabled
    | (the /register page returns 404 and the link is hidden on the login page).
    */

    'code' => env('REGISTRATION_CODE'),

];