<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Verdient\Hyperf3\Struct\Result;

/**
 * 报告器接口
 *
 * @author Verdient。
 */
interface ReporterInterface
{
    /**
     * 报告异常
     *
     * @param ExceptionOccurredEvent $event 异常事件
     *
     * @author Verdient。
     */
    public function report(ExceptionOccurredEvent $event): Result;
}
