<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Logger\LoggerFactory;
use Override;
use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Lock;
use Swoole\Table;
use Throwable;
use Verdient\Hyperf3\Struct\Result;

use function Hyperf\Config\config;
use function Hyperf\Support\make;

/**
 * 报警报告器
 *
 * @author Verdient。
 */
class AlertReporter implements ReporterInterface
{
    /**
     * @var AlertorInterface[] 汇报器集合
     *
     * @author Verdient。
     */
    protected array $alertors = [];

    /**
     * 静默时间
     *
     * @author Verdient。
     */
    protected int $silencePeriod = 0;

    /**
     * 发送的消息的哈希和时间
     *
     * @author Verdient。
     */
    protected static ?Table $hashs = null;

    /**
     * 锁
     *
     * @author Verdient。
     */
    protected static ?Lock $lock = null;

    /**
     * @author Verdient。
     */
    public function __construct(protected readonly LoggerFactory $loggerFactory)
    {
        if (config('alertor.enable', false)) {
            /** @var class-string<AlertorInterface>[] */
            $alertors = array_unique([
                ...config('alertor.alertors', []),
                ...array_keys(AnnotationCollector::getClassesByAnnotation(Alertor::class))
            ]);

            $this->alertors = array_map(function ($reporter) {
                return make($reporter);
            }, $alertors);

            $this->silencePeriod = config('alertor.silence_period', 0);
        }
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function report(ExceptionOccurredEvent $event): Result
    {
        if ($this->silencePeriod > 0 && static::$lock && static::$hashs) {
            static::$lock->lock();

            static::clearInvalidCache();

            $throwable = $event->throwable;

            $hash = md5($throwable->getFile() . ':' . $throwable->getLine());

            if (static::$hashs->exists($hash)) {
                static::$lock->unlock();
                return Result::succeed();
            }

            static::$hashs->set($hash, [
                'expiredAt' => time() + $this->silencePeriod
            ]);

            static::$lock->unlock();
        }

        foreach ($this->alertors as $alertor) {
            Coroutine::create(function () use ($alertor, $event) {
                try {
                    $result = $alertor->alert($event);
                    if (!$result->getIsOK()) {
                        $logger = $this->loggerFactory->get('app');
                        $logger->critical(new RuntimeException($alertor::class . ': ' . $result->getMessage()));
                    }
                } catch (\Throwable $e) {
                    $logger = $this->loggerFactory->get('app');
                    $logger->critical($e);
                }
            });
        }

        return Result::succeed();
    }

    /**
     * 初始化
     *
     * @author Verdient。
     */
    public static function initialize(): void
    {
        if (config('alertor.silence_period', 0) > 0) {
            $table = new Table(64);
            $table->column('expiredAt', Table::TYPE_INT);
            $table->create();
            static::$hashs = $table;
            static::$lock = new Lock();
        }
    }

    /**
     * 清除无效的缓存数据
     *
     * @author Verdient。
     */
    protected static function clearInvalidCache(): void
    {
        $timestamp = time();

        $uselessKeys = [];

        foreach (static::$hashs as $key => $row) {
            if ($timestamp >= $row['expiredAt']) {
                $uselessKeys[] = $key;
            }
        }

        foreach ($uselessKeys as $uselessKey) {
            static::$hashs->del($uselessKey);
        }
    }
}
