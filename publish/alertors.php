<?php

use function Hyperf\Support\env;

return [
    'enable' => env('ALERTORS_ENABLED', env('APP_ENV') === 'prod'),
    'alertors' => []
];
