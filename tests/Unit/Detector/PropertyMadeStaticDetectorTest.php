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

use Phauthentic\BcCheck\Detector\PropertyMadeStaticDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyMadeStaticDetector::class)]
final class PropertyMadeStaticDetectorTest extends TestCase
{
    private PropertyMadeStaticDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new PropertyMadeStaticDetector();
    }

    public function testDetectsPropertyMadeStatic(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyMadeStatic, $breaks[0]->type);
        $this->assertStringContainsString('was made static', $breaks[0]->message);
    }

    public function testDetectsPropertyMadeNonStatic(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: false)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyMadeNonStatic, $breaks[0]->type);
        $this->assertStringContainsString('no longer static', $breaks[0]->message);
    }

    public function testNoBreakWhenStaticUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedProperties(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isStatic: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
