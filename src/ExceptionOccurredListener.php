<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\LoggerFactory;
use Override;
use RuntimeException;
use Swoole\Coroutine;
use Verdient\Hyperf3\Di\Container;

use function Hyperf\Config\config;
use function Hyperf\Support\make;

/**
 * 异常发生监听器
 *
 * @author Verdient。
 */
class ExceptionOccurredListener implements ListenerInterface
{
    /**
     * @var ReporterInterface[] 汇报器集合
     *
     * @author Verdient。
     */
    protected array $reporters = [];

    /**
     * 是否为调试模式
     *
     * @author Verdient。
     */
    protected bool $isDebug;

    /**
     * @author Verdient。
     */
    public function __construct(protected readonly LoggerFactory $loggerFactory)
    {
        if (config('exceptions.reporter.enable', false)) {
            /** @var class-string<ReporterInterface>[] */
            $reporters = array_unique([
                ...config('exceptions.reporter.reporters', []),
                ...array_keys(AnnotationCollector::getClassesByAnnotation(Reporter::class))
            ]);

            $this->reporters = array_map(function ($reporter) {
                return make($reporter);
            }, $reporters);
        }

        $this->isDebug = config('debug', false);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function listen(): array
    {
        return [
            ExceptionOccurredEvent::class,
        ];
    }

    /**
     * @param ExceptionOccurredEvent $event
     *
     * @author Verdient。
     */
    #[Override]
    public function process(object $event): void
    {
        if ($this->isDebug) {
            if ($logger = Container::getOrNull(StdoutLoggerInterface::class)) {
                $formatter = Container::getOrNull(FormatterInterface::class);
                $logger->error($formatter ? $formatter->format($event->throwable) : $event->throwable);
            }
        }

        foreach ($this->reporters as $reporter) {
            Coroutine::create(function () use ($reporter, $event) {
                try {
                    $result = $reporter->report($event);
                    if (!$result->getIsOK()) {
                        $logger = $this->loggerFactory->get('app');
                        $logger->critical(new RuntimeException($reporter::class . ': ' . $result->getMessage()));
                    }
                } catch (\Throwable $e) {
                    $logger = $this->loggerFactory->get('app');
                    $logger->critical($e);
                }
            });
        }
    }
}
