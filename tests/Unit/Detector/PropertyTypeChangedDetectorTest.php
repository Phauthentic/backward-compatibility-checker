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

use Phauthentic\BcCheck\Detector\PropertyTypeChangedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyTypeChangedDetector::class)]
final class PropertyTypeChangedDetectorTest extends TestCase
{
    private PropertyTypeChangedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new PropertyTypeChangedDetector();
    }

    public function testDetectsTypeAdded(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: null)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'string'))],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyTypeChanged, $breaks[0]->type);
        $this->assertStringContainsString('now has type', $breaks[0]->message);
    }

    public function testDetectsTypeChanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'string'))],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'int'))],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyTypeChanged, $breaks[0]->type);
        $this->assertStringContainsString('changed from', $breaks[0]->message);
    }

    public function testNoBreakWhenTypeUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'string'))],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'string'))],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenBothUntyped(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: null)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: null)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedProperties(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, type: new TypeInfo(name: 'string'))],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
