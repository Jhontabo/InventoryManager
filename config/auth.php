<?php

// config/auth.php
return [


    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',  // Asegúrate de que el provider sea el correcto
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,  // Modelo correcto
        ],
    ],


];
