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

final readonly class ConstantVisibilityReducedDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedConstants() as $beforeConstant) {
            $afterConstant = $after->getConstant($beforeConstant->name);

            if ($afterConstant === null) {
                continue;
            }

            if ($afterConstant->visibility->isMoreRestrictiveThan($beforeConstant->visibility)) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Constant %s::%s visibility reduced from %s to %s',
                        $before->name,
                        $beforeConstant->name,
                        $beforeConstant->visibility->value,
                        $afterConstant->visibility->value,
                    ),
                    className: $before->name,
                    memberName: $beforeConstant->name,
                    type: BcBreakType::ConstantVisibilityReduced,
                );
            }
        }

        return $breaks;
    }
}
