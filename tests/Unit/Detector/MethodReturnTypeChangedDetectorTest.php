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

use Phauthentic\BcCheck\Detector\MethodReturnTypeChangedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodReturnTypeChangedDetector::class)]
final class MethodReturnTypeChangedDetectorTest extends TestCase
{
    private MethodReturnTypeChangedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodReturnTypeChangedDetector();
    }

    public function testDetectsReturnTypeRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: null,
            )],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodReturnTypeChanged, $breaks[0]->type);
        $this->assertStringContainsString('was removed', $breaks[0]->message);
    }

    public function testDetectsReturnTypeChanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'int'),
            )],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodReturnTypeChanged, $breaks[0]->type);
        $this->assertStringContainsString('changed from', $breaks[0]->message);
        $this->assertStringContainsString('string', $breaks[0]->message);
        $this->assertStringContainsString('int', $breaks[0]->message);
    }

    public function testDetectsNullabilityChanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string', isNullable: false),
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string', isNullable: true),
            )],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
    }

    public function testNoBreakWhenReturnTypeUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenReturnTypeAdded(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: null,
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(
                name: 'handle',
                visibility: Visibility::Public,
                returnType: new TypeInfo(name: 'string'),
            )],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
