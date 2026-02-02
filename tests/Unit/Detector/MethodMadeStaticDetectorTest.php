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

use Phauthentic\BcCheck\Detector\MethodMadeStaticDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodMadeStaticDetector::class)]
final class MethodMadeStaticDetectorTest extends TestCase
{
    private MethodMadeStaticDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodMadeStaticDetector();
    }

    public function testDetectsMethodMadeStatic(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodMadeStatic, $breaks[0]->type);
        $this->assertStringContainsString('was made static', $breaks[0]->message);
    }

    public function testDetectsMethodMadeNonStatic(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: false)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodMadeNonStatic, $breaks[0]->type);
        $this->assertStringContainsString('no longer static', $breaks[0]->message);
    }

    public function testNoBreakWhenStaticUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isStatic: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
