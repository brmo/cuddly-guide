<?php

return [
    'prefix' => 'api',
    'domain' => null,
    'middleware' => [
        'api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
