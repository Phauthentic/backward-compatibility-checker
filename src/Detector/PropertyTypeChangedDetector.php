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

final readonly class PropertyTypeChangedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedProperties() as $beforeProperty) {
            $afterProperty = $after->getProperty($beforeProperty->name);

            if ($afterProperty === null) {
                continue;
            }

            // Type added (was untyped)
            if ($beforeProperty->type === null && $afterProperty->type !== null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Property %s::$%s now has type %s',
                        $before->name,
                        $beforeProperty->name,
                        $afterProperty->type->toString(),
                    ),
                    className: $before->name,
                    memberName: $beforeProperty->name,
                    type: BcBreakType::PropertyTypeChanged,
                );

                continue;
            }

            // Type changed
            if (
                $beforeProperty->type !== null
                && $afterProperty->type !== null
                && !$beforeProperty->type->equals($afterProperty->type)
            ) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Property %s::$%s type changed from %s to %s',
                        $before->name,
                        $beforeProperty->name,
                        $beforeProperty->type->toString(),
                        $afterProperty->type->toString(),
                    ),
                    className: $before->name,
                    memberName: $beforeProperty->name,
                    type: BcBreakType::PropertyTypeChanged,
                );
            }
        }

        return $breaks;
    }
}
