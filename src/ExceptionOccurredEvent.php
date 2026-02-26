<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use Swow\Psr7\Message\ResponsePlusInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;
use Throwable;

use function Hyperf\Config\config;

/**
 * 异常发生事件
 *
 * @author Verdient。
 */
class ExceptionOccurredEvent
{
    /**
     * 请求
     *
     * @author Verdient。
     */
    public readonly ?ServerRequestPlusInterface $request;

    /**
     * 响应
     *
     * @author Verdient。
     */
    public readonly ?ResponsePlusInterface $response;

    /**
     * App名称
     *
     * @author Verdient。
     */
    public readonly ?string $appName;

    /**
     * App环境
     *
     * @author Verdient。
     */
    public readonly ?string $appEnv;

    /**
     * @param Throwable $throwable 异常
     *
     * @author Verdient。
     */
    public function __construct(public readonly Throwable $throwable)
    {
        $this->request = RequestContext::getOrNull();

        $this->response = ResponseContext::getOrNull();

        $this->appName = config('app_name');

        $this->appEnv = config('app_env');
    }
}
