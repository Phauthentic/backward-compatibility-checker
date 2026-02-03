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
use Phauthentic\BcCheck\ValueObject\ClassInfo;

final class DetectorRegistry
{
    /** @var list<BcBreakDetectorInterface> */
    private array $detectors = [];

    /**
     * @param list<BcBreakDetectorInterface> $detectors
     */
    public function __construct(array $detectors = [])
    {
        $this->detectors = $detectors;
    }

    public function register(BcBreakDetectorInterface $detector): void
    {
        $this->detectors[] = $detector;
    }

    /**
     * @return list<BcBreak>
     */
    public function detectAll(ClassInfo $before, ClassInfo $after, ?RenameMap $renameMap = null): array
    {
        $breaks = [];

        foreach ($this->detectors as $detector) {
            // Pass rename map to detectors that support it
            if ($detector instanceof RenameAwareDetectorInterface) {
                $detector->setRenameMap($renameMap);
            }

            $detected = $detector->detect($before, $after);
            foreach ($detected as $break) {
                $breaks[] = $break;
            }
        }

        return $breaks;
    }

    /**
     * @return list<BcBreakDetectorInterface>
     */
    public function getDetectors(): array
    {
        return $this->detectors;
    }
}
