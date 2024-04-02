<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                ExceptionOccurredListener::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for exception alertors.',
                    'source' => dirname(__DIR__) . '/publish/alertors.php',
                    'destination' => constant('BASE_PATH') . '/config/autoload/alertors.php',
                ]
            ]
        ];
    }
}
