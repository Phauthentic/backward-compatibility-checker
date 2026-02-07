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

use Phauthentic\BcCheck\Diff\RenameMap;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;

final class PropertyRemovedDetector implements RenameAwareDetectorInterface
{
    private ?RenameMap $renameMap = null;

    public function setRenameMap(?RenameMap $map): void
    {
        $this->renameMap = $map;
    }

    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        foreach ($before->getPublicOrProtectedProperties() as $property) {
            $afterProperty = $after->getProperty($property->name);

            if ($afterProperty !== null) {
                continue;
            }

            // Check if this was a rename
            $newName = $this->renameMap?->getPropertyNewName($property->name);

            if ($newName !== null && $after->getProperty($newName) !== null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        '%s property %s::$%s was renamed to $%s',
                        $property->visibility === Visibility::Public ? 'Public' : 'Protected',
                        $before->name,
                        $property->name,
                        $newName,
                    ),
                    className: $before->name,
                    memberName: $property->name,
                    type: BcBreakType::PropertyRenamed,
                );
            } else {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        '%s property %s::$%s was removed',
                        $property->visibility === Visibility::Public ? 'Public' : 'Protected',
                        $before->name,
                        $property->name,
                    ),
                    className: $before->name,
                    memberName: $property->name,
                    type: BcBreakType::PropertyRemoved,
                );
            }
        }

        return $breaks;
    }
}
