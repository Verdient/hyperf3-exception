<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Verdient\Hyperf3\Struct\Result;

/**
 * 报警器接口
 * @author Verdient。
 */
interface AlertorInterface
{
    /**
     * 报警
     * @author Verdient。
     */
    public static function alert(string $message): Result;
}
