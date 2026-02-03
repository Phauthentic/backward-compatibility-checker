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

use Phauthentic\BcCheck\Diff\RenameMap;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;

final class MethodRemovedDetector implements RenameAwareDetectorInterface
{
    private ?RenameMap $renameMap = null;

    public function setRenameMap(?RenameMap $map): void
    {
        $this->renameMap = $map;
    }

    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedMethods() as $method) {
            $afterMethod = $after->getMethod($method->name);

            if ($afterMethod !== null) {
                continue;
            }

            // Check if this was a rename
            $newName = $this->renameMap?->getMethodNewName($method->name);

            if ($newName !== null && $after->getMethod($newName) !== null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        '%s method %s::%s() was renamed to %s()',
                        $method->visibility === Visibility::Public ? 'Public' : 'Protected',
                        $before->name,
                        $method->name,
                        $newName,
                    ),
                    className: $before->name,
                    memberName: $method->name,
                    type: BcBreakType::MethodRenamed,
                );
            } else {
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
