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

use Phauthentic\BcCheck\Detector\MethodVisibilityReducedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodVisibilityReducedDetector::class)]
final class MethodVisibilityReducedDetectorTest extends TestCase
{
    private MethodVisibilityReducedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodVisibilityReducedDetector();
    }

    public function testDetectsPublicToProtected(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Protected)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodVisibilityReduced, $breaks[0]->type);
        $this->assertStringContainsString('public', $breaks[0]->message);
        $this->assertStringContainsString('protected', $breaks[0]->message);
    }

    public function testDetectsPublicToPrivate(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Private)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodVisibilityReduced, $breaks[0]->type);
    }

    public function testDetectsProtectedToPrivate(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Protected)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Private)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
    }

    public function testNoBreakWhenVisibilityUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenVisibilityIncreased(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Protected)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
