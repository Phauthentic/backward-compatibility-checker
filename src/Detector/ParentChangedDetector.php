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

final readonly class ParentChangedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        // If before had a parent and after doesn't, or they're different
        if ($before->parentClass !== null && $before->parentClass !== $after->parentClass) {
            if ($after->parentClass === null) {
                return [
                    new BcBreak(
                        message: sprintf(
                            'Class %s no longer extends %s',
                            $before->name,
                            $before->parentClass,
                        ),
                        className: $before->name,
                        type: BcBreakType::ParentChanged,
                    ),
                ];
            }

            return [
                new BcBreak(
                    message: sprintf(
                        'Class %s changed parent from %s to %s',
                        $before->name,
                        $before->parentClass,
                        $after->parentClass,
                    ),
                    className: $before->name,
                    type: BcBreakType::ParentChanged,
                ),
            ];
        }

        return [];
    }
}
