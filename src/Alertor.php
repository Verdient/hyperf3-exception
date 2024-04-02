<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Exception;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 报警器
 * @author Verdient。
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Alertor extends AbstractAnnotation
{
}
