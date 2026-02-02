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

use Phauthentic\BcCheck\Detector\MethodMadeAbstractDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodMadeAbstractDetector::class)]
final class MethodMadeAbstractDetectorTest extends TestCase
{
    private MethodMadeAbstractDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodMadeAbstractDetector();
    }

    public function testDetectsMethodMadeAbstract(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodMadeAbstract, $breaks[0]->type);
        $this->assertStringContainsString('abstract', $breaks[0]->message);
    }

    public function testNoBreakWhenAlreadyAbstract(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: true)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenAbstractRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: true)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: false)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [new MethodInfo(name: 'handle', visibility: Visibility::Public, isAbstract: false)],
        );
        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
