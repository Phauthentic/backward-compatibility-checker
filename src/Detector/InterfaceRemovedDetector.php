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

final readonly class InterfaceRemovedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->interfaces as $interface) {
            if (!$after->hasInterface($interface)) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Class %s no longer implements interface %s',
                        $before->name,
                        $interface,
                    ),
                    className: $before->name,
                    type: BcBreakType::InterfaceRemoved,
                );
            }
        }

        return $breaks;
    }
}
