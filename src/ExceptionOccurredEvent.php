<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Throwable;

/**
 * 异常发生事件
 * @author Verdient。
 */
class ExceptionOccurredEvent
{
    /**
     * @var string 消息
     * @author Verdient。
     */
    public $message;

    /**
     * @var string 类型
     * @author Verdient。
     */
    public $type;

    /**
     * @var string 文件
     * @author Verdient。
     */
    public $file;

    /**
     * @var int 行号
     * @author Verdient。
     */
    public $line;

    /**
     * @var array 堆栈信息
     * @author Verdient。
     */
    public $trace = [];

    /**
     * @param Throwable|array|string $exception 异常
     * @author Verdient。
     */
    public function __construct($exception)
    {
        if (is_array($exception)) {
            $this->message = $exception['message'];
            $this->type = $exception['type'] ?? null;
            $this->file = $exception['file'] ?? null;
            $this->line = $exception['line'] ?? null;
            $this->trace = $exception['trace'] ?? [];
        } else if (is_string($exception)) {
            $this->message = $exception;
        } else {
            $this->message = $exception->getMessage();
            $this->type = get_class($exception);
            $this->file = $exception->getFile();
            $this->line = $exception->getLine();
            $this->trace = $exception->getTrace();
        }
    }
}
