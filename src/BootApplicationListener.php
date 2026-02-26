<?php

namespace Verdient\Hyperf3\Exception;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Override;

/**
 * 启动应用监听器
 *
 * @author Verdient。
 */
#[Listener]
class BootApplicationListener implements ListenerInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param BootApplication $event
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {
        $command = $_SERVER['argv'][1] ?? null;

        if ($command !== 'start') {
            return;
        }

        AlertReporter::initialize();
    }
}
