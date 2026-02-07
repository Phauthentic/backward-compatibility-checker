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

use Phauthentic\BcCheck\Detector\MethodRemovedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodRemovedDetector::class)]
final class MethodRemovedDetectorTest extends TestCase
{
    private MethodRemovedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodRemovedDetector();
    }

    public function testDetectsRemovedPublicMethod(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'handle', visibility: Visibility::Public),
                new MethodInfo(name: 'process', visibility: Visibility::Public),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'process', visibility: Visibility::Public),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodRemoved, $breaks[0]->type);
        $this->assertStringContainsString('handle', $breaks[0]->message);
    }

    public function testDetectsRemovedProtectedMethod(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'internalMethod', visibility: Visibility::Protected),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodRemoved, $breaks[0]->type);
    }

    public function testIgnoresPrivateMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'privateMethod', visibility: Visibility::Private),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreaksWhenMethodsRemain(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'handle', visibility: Visibility::Public),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(name: 'handle', visibility: Visibility::Public),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
