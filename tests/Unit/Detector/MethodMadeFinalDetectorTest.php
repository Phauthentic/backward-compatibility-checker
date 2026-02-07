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

use Phauthentic\BcCheck\Detector\MethodMadeFinalDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodMadeFinalDetector::class)]
final class MethodMadeFinalDetectorTest extends TestCase
{
    private MethodMadeFinalDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodMadeFinalDetector();
    }

    public function testDetectsMethodMadeFinal(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodMadeFinal, $breaks[0]->type);
        $this->assertStringContainsString('final', $breaks[0]->message);
    }

    public function testNoBreakWhenAlreadyFinal(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenFinalRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: false)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isFinal: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
