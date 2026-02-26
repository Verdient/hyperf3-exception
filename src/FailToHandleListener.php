<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Hyperf\Command\Event\FailToHandle;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Override;
use Verdient\Hyperf3\Di\Container;
use Verdient\Hyperf3\Event\Event;

/**
 * 处理失败事件监听器
 *
 * @author Verdient。
 */
class FailToHandleListener implements ListenerInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function listen(): array
    {
        return [
            FailToHandle::class,
        ];
    }

    /**
     * @param FailToHandle $event
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {

        Event::dispatch(new ExceptionOccurredEvent($event->getThrowable()));

        if ($logger = Container::getOrNull(StdoutLoggerInterface::class)) {
            $formatter = Container::getOrNull(FormatterInterface::class);
            $logger->error($formatter ? $formatter->format($event->getThrowable()) : $event->getThrowable());
        }
    }
}
