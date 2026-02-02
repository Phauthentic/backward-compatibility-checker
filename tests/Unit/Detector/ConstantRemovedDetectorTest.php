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

use Phauthentic\BcCheck\Detector\ConstantRemovedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\ConstantInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConstantRemovedDetector::class)]
final class ConstantRemovedDetectorTest extends TestCase
{
    private ConstantRemovedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new ConstantRemovedDetector();
    }

    public function testDetectsPublicConstantRemoved(): void
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

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::ConstantRemoved, $breaks[0]->type);
        $this->assertStringContainsString('Public', $breaks[0]->message);
        $this->assertStringContainsString('VERSION', $breaks[0]->message);
    }

    public function testDetectsProtectedConstantRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'INTERNAL', visibility: Visibility::Protected)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('Protected', $breaks[0]->message);
    }

    public function testIgnoresPrivateConstants(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'SECRET', visibility: Visibility::Private)],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenConstantsUnchanged(): void
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

    public function testNoBreakWhenConstantAdded(): void
    {
        $before = new ClassInfo(
            name: 'App\\Config',
            constants: [],
        );
        $after = new ClassInfo(
            name: 'App\\Config',
            constants: [new ConstantInfo(name: 'VERSION', visibility: Visibility::Public)],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
