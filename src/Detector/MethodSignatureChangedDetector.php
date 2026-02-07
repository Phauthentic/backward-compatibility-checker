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
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\ParameterInfo;

final readonly class MethodSignatureChangedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedMethods() as $beforeMethod) {
            $afterMethod = $after->getMethod($beforeMethod->name);

            if ($afterMethod === null) {
                // Method removed - handled by MethodRemovedDetector
                continue;
            }

            $methodBreaks = $this->compareSignatures($before->name, $beforeMethod, $afterMethod);
            foreach ($methodBreaks as $break) {
                $breaks[] = $break;
            }
        }

        return $breaks;
    }

    /**
     * @return list<BcBreak>
     */
    private function compareSignatures(string $className, MethodInfo $before, MethodInfo $after): array
    {
        $breaks = [];

        // Check if new required parameters were added
        $beforeRequired = $before->getRequiredParameterCount();
        $afterRequired = $after->getRequiredParameterCount();

        if ($afterRequired > $beforeRequired) {
            $breaks[] = new BcBreak(
                message: sprintf(
                    'Method %s::%s() has more required parameters (%d -> %d)',
                    $className,
                    $before->name,
                    $beforeRequired,
                    $afterRequired,
                ),
                className: $className,
                memberName: $before->name,
                type: BcBreakType::MethodSignatureChanged,
            );
        }

        // Check parameter type changes for existing parameters
        $minCount = min(count($before->parameters), count($after->parameters));

        for ($i = 0; $i < $minCount; $i++) {
            $beforeParam = $before->parameters[$i];
            $afterParam = $after->parameters[$i];

            $paramBreaks = $this->compareParameters($className, $before->name, $beforeParam, $afterParam, $i);
            foreach ($paramBreaks as $break) {
                $breaks[] = $break;
            }
        }

        return $breaks;
    }

    /**
     * @return list<BcBreak>
     */
    private function compareParameters(
        string $className,
        string $methodName,
        ParameterInfo $before,
        ParameterInfo $after,
        int $position,
    ): array {
        $breaks = [];

        // Parameter type changed (added or made more restrictive)
        if ($before->type === null && $after->type !== null) {
            $breaks[] = new BcBreak(
                message: sprintf(
                    'Parameter $%s of %s::%s() now has type %s',
                    $before->name,
                    $className,
                    $methodName,
                    $after->type->toString(),
                ),
                className: $className,
                memberName: $methodName,
                type: BcBreakType::MethodSignatureChanged,
            );
        } elseif ($before->type !== null && $after->type !== null && !$before->type->equals($after->type)) {
            $breaks[] = new BcBreak(
                message: sprintf(
                    'Parameter $%s of %s::%s() changed type from %s to %s',
                    $before->name,
                    $className,
                    $methodName,
                    $before->type->toString(),
                    $after->type->toString(),
                ),
                className: $className,
                memberName: $methodName,
                type: BcBreakType::MethodSignatureChanged,
            );
        }

        // Default removed (was optional, now required)
        if ($before->hasDefault && !$after->hasDefault) {
            $breaks[] = new BcBreak(
                message: sprintf(
                    'Parameter $%s of %s::%s() is no longer optional',
                    $before->name,
                    $className,
                    $methodName,
                ),
                className: $className,
                memberName: $methodName,
                type: BcBreakType::MethodSignatureChanged,
            );
        }

        // By-reference changed
        if ($before->isByReference !== $after->isByReference) {
            $breaks[] = new BcBreak(
                message: sprintf(
                    'Parameter $%s of %s::%s() %s',
                    $before->name,
                    $className,
                    $methodName,
                    $after->isByReference ? 'is now passed by reference' : 'is no longer passed by reference',
                ),
                className: $className,
                memberName: $methodName,
                type: BcBreakType::MethodSignatureChanged,
            );
        }

        return $breaks;
    }
}
