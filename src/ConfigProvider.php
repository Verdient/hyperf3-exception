<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'exceptions' => [
                'reporter' => [
                    'reporters' => [
                        AlertReporter::class
                    ]
                ]
            ],
            'listeners' => [
                BootApplicationListener::class,
                ExceptionOccurredListener::class,
                FailToHandleListener::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for exception alertors.',
                    'source' => dirname(__DIR__) . '/publish/alertor.php',
                    'destination' => constant('BASE_PATH') . '/config/autoload/alertor.php',
                ]
            ]
        ];
    }
}
