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

use Phauthentic\BcCheck\Detector\ConstantVisibilityReducedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\ConstantInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConstantVisibilityReducedDetector::class)]
final class ConstantVisibilityReducedDetectorTest extends TestCase
{
    private ConstantVisibilityReducedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new ConstantVisibilityReducedDetector();
    }

    public function testDetectsPublicToProtected(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Protected)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::ConstantVisibilityReduced, $breaks[0]->type);
        $this->assertStringContainsString('public', $breaks[0]->message);
        $this->assertStringContainsString('protected', $breaks[0]->message);
    }

    public function testDetectsPublicToPrivate(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Private)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
    }

    public function testNoBreakWhenVisibilityUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenVisibilityIncreased(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Protected)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testSkipsRemovedConstants(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
