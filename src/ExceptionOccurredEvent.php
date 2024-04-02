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
    public string $message;

    /**
     * @var string 类型
     * @author Verdient。
     */
    public ?string $type;

    /**
     * @var string 文件
     * @author Verdient。
     */
    public ?string $file;

    /**
     * @var int 行号
     * @author Verdient。
     */
    public ?int $line;

    /**
     * @var array 堆栈信息
     * @author Verdient。
     */
    public array $trace = [];

    /**
     * @param Throwable|array|string $exception 异常
     * @author Verdient。
     */
    public function __construct(Throwable|array|string $exception)
    {
        if (is_array($exception)) {
            $this->message = (string) $exception['message'];
            $this->type = empty($exception['type']) ? null : (string) $exception['type'];
            $this->file = empty($exception['file']) ? null : (string) $exception['file'];
            $this->line = empty($exception['line']) ? null : (int) $exception['line'];
            $this->trace = empty($exception['trace']) ? [] : (array) $exception['trace'];
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
