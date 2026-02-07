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

namespace Phauthentic\BcCheck\Tests\Unit\Detector;

use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\Detector\DetectorRegistry;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DetectorRegistry::class)]
final class DetectorRegistryTest extends TestCase
{
    public function testRegisterAddsDetector(): void
    {
        $registry = new DetectorRegistry();
        $detector = $this->createStub(BcBreakDetectorInterface::class);

        $registry->register($detector);

        $this->assertCount(1, $registry->getDetectors());
    }

    public function testConstructorAcceptsDetectors(): void
    {
        $detector1 = $this->createStub(BcBreakDetectorInterface::class);
        $detector2 = $this->createStub(BcBreakDetectorInterface::class);

        $registry = new DetectorRegistry([$detector1, $detector2]);

        $this->assertCount(2, $registry->getDetectors());
    }

    public function testDetectAllAggregatesBreaks(): void
    {
        $before = new ClassInfo(name: 'App\\Service');
        $after = new ClassInfo(name: 'App\\Service');

        $break1 = new BcBreak(
            message: 'Break 1',
            className: 'App\\Service',
            type: BcBreakType::MethodRemoved,
        );
        $break2 = new BcBreak(
            message: 'Break 2',
            className: 'App\\Service',
            type: BcBreakType::PropertyRemoved,
        );

        $detector1 = $this->createStub(BcBreakDetectorInterface::class);
        $detector1->method('detect')->willReturn([$break1]);

        $detector2 = $this->createStub(BcBreakDetectorInterface::class);
        $detector2->method('detect')->willReturn([$break2]);

        $registry = new DetectorRegistry([$detector1, $detector2]);

        $breaks = $registry->detectAll($before, $after);

        $this->assertCount(2, $breaks);
        $this->assertSame($break1, $breaks[0]);
        $this->assertSame($break2, $breaks[1]);
    }

    public function testDetectAllWithNoDetectors(): void
    {
        $registry = new DetectorRegistry();

        $breaks = $registry->detectAll(
            new ClassInfo(name: 'App\\Service'),
            new ClassInfo(name: 'App\\Service'),
        );

        $this->assertCount(0, $breaks);
    }
}
