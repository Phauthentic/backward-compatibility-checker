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

final readonly class PropertyVisibilityReducedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedProperties() as $beforeProperty) {
            $afterProperty = $after->getProperty($beforeProperty->name);

            if ($afterProperty === null) {
                continue;
            }

            if ($afterProperty->visibility->isMoreRestrictiveThan($beforeProperty->visibility)) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Property %s::$%s visibility reduced from %s to %s',
                        $before->name,
                        $beforeProperty->name,
                        $beforeProperty->visibility->value,
                        $afterProperty->visibility->value,
                    ),
                    className: $before->name,
                    memberName: $beforeProperty->name,
                    type: BcBreakType::PropertyVisibilityReduced,
                );
            }
        }

        return $breaks;
    }
}
