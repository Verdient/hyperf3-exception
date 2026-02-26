<?php

use function Hyperf\Support\env;

return [
    'enable' => env('ALERTOR_ENABLE', env('APP_ENV') === 'prod'),
    'silence_period' => env('ALERTOR_SILENCE_PERIOD', 30),
    'alertors' => []
];
