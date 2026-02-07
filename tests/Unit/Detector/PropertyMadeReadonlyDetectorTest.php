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

use Phauthentic\BcCheck\Detector\PropertyMadeReadonlyDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyMadeReadonlyDetector::class)]
final class PropertyMadeReadonlyDetectorTest extends TestCase
{
    private PropertyMadeReadonlyDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new PropertyMadeReadonlyDetector();
    }

    public function testDetectsPropertyMadeReadonly(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyMadeReadonly, $breaks[0]->type);
        $this->assertStringContainsString('readonly', $breaks[0]->message);
    }

    public function testNoBreakWhenAlreadyReadonly(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenReadonlyRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: false)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedProperties(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [new PropertyInfo(name: 'name', visibility: Visibility::Public, isReadonly: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
