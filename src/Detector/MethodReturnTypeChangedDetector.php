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

final readonly class MethodReturnTypeChangedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedMethods() as $beforeMethod) {
            $afterMethod = $after->getMethod($beforeMethod->name);

            if ($afterMethod === null) {
                continue;
            }

            // Return type removed - could break implementations
            if ($beforeMethod->returnType !== null && $afterMethod->returnType === null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Method %s::%s() return type %s was removed',
                        $before->name,
                        $beforeMethod->name,
                        $beforeMethod->returnType->toString(),
                    ),
                    className: $before->name,
                    memberName: $beforeMethod->name,
                    type: BcBreakType::MethodReturnTypeChanged,
                );

                continue;
            }

            // Return type changed
            if (
                $beforeMethod->returnType !== null
                && $afterMethod->returnType !== null
                && !$beforeMethod->returnType->equals($afterMethod->returnType)
            ) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Method %s::%s() return type changed from %s to %s',
                        $before->name,
                        $beforeMethod->name,
                        $beforeMethod->returnType->toString(),
                        $afterMethod->returnType->toString(),
                    ),
                    className: $before->name,
                    memberName: $beforeMethod->name,
                    type: BcBreakType::MethodReturnTypeChanged,
                );
            }
        }

        return $breaks;
    }
}
