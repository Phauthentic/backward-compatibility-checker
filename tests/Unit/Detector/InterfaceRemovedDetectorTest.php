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

namespace Phauthentic\BcCheck\Tests\Unit\Detector;

use Phauthentic\BcCheck\Detector\InterfaceRemovedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InterfaceRemovedDetector::class)]
final class InterfaceRemovedDetectorTest extends TestCase
{
    private InterfaceRemovedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new InterfaceRemovedDetector();
    }

    public function testDetectsInterfaceRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['App\\Contracts\\ServiceInterface', 'App\\Contracts\\LoggerInterface'],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['App\\Contracts\\ServiceInterface'],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::InterfaceRemoved, $breaks[0]->type);
        $this->assertStringContainsString('LoggerInterface', $breaks[0]->message);
    }

    public function testDetectsMultipleInterfacesRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA', 'InterfaceB', 'InterfaceC'],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA'],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(2, $breaks);
    }

    public function testNoBreakWhenInterfacesUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA', 'InterfaceB'],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA', 'InterfaceB'],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenInterfaceAdded(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA'],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['InterfaceA', 'InterfaceB'],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenNoInterfaces(): void
    {
        $before = new ClassInfo(name: 'App\\Service', interfaces: []);
        $after = new ClassInfo(name: 'App\\Service', interfaces: []);

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
