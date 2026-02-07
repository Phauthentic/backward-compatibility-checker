<?php

declare(strict_types=1);

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/GPL-3.0 GPL License
 */

namespace Phauthentic\BcCheck\Detector;

use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;

final readonly class MethodRemovedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedMethods() as $method) {
            $afterMethod = $after->getMethod($method->name);

            if ($afterMethod === null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        '%s method %s::%s() was removed',
                        $method->visibility === Visibility::Public ? 'Public' : 'Protected',
                        $before->name,
                        $method->name,
                    ),
                    className: $before->name,
                    memberName: $method->name,
                    type: BcBreakType::MethodRemoved,
                );
            }
        }

        return $breaks;
    }
}
