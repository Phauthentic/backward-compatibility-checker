<?php

declare(strict_types=1);

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The GPL License
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

final readonly class MethodMadeStaticDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedMethods() as $beforeMethod) {
            $afterMethod = $after->getMethod($beforeMethod->name);

            if ($afterMethod === null) {
                continue;
            }

            if (!$beforeMethod->isStatic && $afterMethod->isStatic) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Method %s::%s() was made static',
                        $before->name,
                        $beforeMethod->name,
                    ),
                    className: $before->name,
                    memberName: $beforeMethod->name,
                    type: BcBreakType::MethodMadeStatic,
                );
            }

            if ($beforeMethod->isStatic && !$afterMethod->isStatic) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Method %s::%s() is no longer static',
                        $before->name,
                        $beforeMethod->name,
                    ),
                    className: $before->name,
                    memberName: $beforeMethod->name,
                    type: BcBreakType::MethodMadeNonStatic,
                );
            }
        }

        return $breaks;
    }
}
