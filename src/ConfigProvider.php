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
            ]
        ];
    }
}
