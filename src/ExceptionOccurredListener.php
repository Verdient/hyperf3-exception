<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use SplFileObject;
use Swoole\Coroutine;
use Verdient\Hyperf3\Logger\HasLogger;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * 异常发生监听器
 * @author Verdient。
 */
class ExceptionOccurredListener implements ListenerInterface
{
    use HasLogger;

    /**
     * @var bool 是否发送异常消息
     * @author Verdient。
     */
    protected bool $enable = false;

    /**
     * @var string[] 发送的消息的哈希和时间
     * @author Verdient。
     */
    protected array $hashs = [];

    /**
     * @var int 静默时间
     * @author Verdient。
     */
    protected $silence = 7200;

    /**
     * @var int 上次发送时间
     * @author Verdient。
     */
    protected $lastSendAt = null;

    /**
     * @author Verdient。
     */
    public function __construct()
    {
        $this->enable = config('alertors.enable', true);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function listen(): array
    {
        return [
            ExceptionOccurredEvent::class,
        ];
    }

    /**
     * @param ExceptionOccurredEvent $event
     * @author Verdient。
     */
    public function process(object $event): void
    {
        if (!$this->enable) {
            return;
        }
        $name = config('app_name');
        $env = config('app_env');
        $identifier = env('IDENTIFIER', 'Unknown environment');
        if ($event->file && $event->line) {
            $key = $event->file . ':' . $event->line;
        } else {
            $key = $event->message;
        }
        if (!$this->should($key)) {
            return;
        }
        $traceString = null;
        if (defined('BASE_PATH')) {
            $baseDir = constant('BASE_PATH') . DIRECTORY_SEPARATOR;
        } else {
            $baseDir = '';
        }
        if (!empty($event->trace)) {
            $traces = [];
            $vendorDir = $baseDir . 'vendor';
            foreach ($event->trace as $trace) {
                foreach (['file', 'line', 'class', 'type', 'function'] as $attribute) {
                    if (!isset($trace[$attribute])) {
                        continue 2;
                    }
                }
                if (Str::startsWith($trace['file'],  $vendorDir)) {
                    continue;
                }
                $traces[] = $trace;
            }
            if (!empty($traces)) {
                foreach ($traces as $index => $trace) {
                    $file = substr($trace['file'], strlen($baseDir));
                    $line = $trace['line'];
                    $class = $trace['class'];
                    $type = $trace['type'];
                    $function = $trace['function'];
                    $traceString .= "\n#$index $file($line): $class$type$function()";
                }
                $traceString = substr($traceString, 1);
            }
        }
        $file = $event->file ? substr($event->file, strlen($baseDir)) : null;
        $line = $event->line;
        $type = $event->type;
        if ($event->type) {
            $alert = "[炸弹] $name [$identifier] [$env] [$type] ‼️";
        } else {
            $alert = "[炸弹] $name [$identifier] [$env] ‼️";
        }
        $alert .= "\n$event->message";
        if ($line) {
            $alert .= "\nin $file:$line";
            $phpFile = new SplFileObject($baseDir . $file, 'r');
            $phpFile->seek($line - 1);
            $snippet = trim($phpFile->current());
            $alert .= "\n\n$snippet";
        } else if ($file) {
            $alert .= "\nin $file";
        }
        if ($traceString) {
            $alert .= "\n\n$traceString";
        }
        $this->alertDevelopers($alert);
    }

    /**
     * 判断是否要发送
     * @param string $content 消息内容
     * @return bool
     * @author Verdient。
     */
    protected function should($content)
    {
        $now = time();
        $contentHash = hash('sha256', $content);
        foreach ($this->hashs as $hash => $time) {
            $endAt = $now - $this->silence;
            if ($time < $endAt) {
                unset($this->hashs[$hash]);
            }
        }
        if (!isset($this->hashs[$contentHash])) {
            $this->hashs[$contentHash] = $now;
            $this->lastSendAt = $now;
            return true;
        }
        return false;
    }

    /**
     * 提醒开发者
     * @param string $message 提示信息
     * @author Verdient。
     */
    public function alertDevelopers(string $message)
    {
        /** @var AlertorInterface[] */
        $alertors = array_unique([
            ...config('alertors.alertors', []),
            ...array_keys(AnnotationCollector::getClassesByAnnotation(Alertor::class))
        ]);

        foreach ($alertors as $alertor) {
            Coroutine::create(function () use ($message, $alertor) {
                try {
                    $result = $alertor::alert($message);
                    if (!$result->getIsOK()) {
                        $this->logger()->emergency($result->getMessage());
                    }
                } catch (\Throwable $e) {
                    $this->logger()->emergency($e);
                }
            });
        }
    }
}
