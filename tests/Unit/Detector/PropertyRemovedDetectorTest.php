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

use Phauthentic\BcCheck\Detector\PropertyRemovedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyRemovedDetector::class)]
final class PropertyRemovedDetectorTest extends TestCase
{
    private PropertyRemovedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new PropertyRemovedDetector();
    }

    public function testDetectsRemovedPublicProperty(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [
                new PropertyInfo(name: 'name', visibility: Visibility::Public),
                new PropertyInfo(name: 'email', visibility: Visibility::Public),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [
                new PropertyInfo(name: 'name', visibility: Visibility::Public),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::PropertyRemoved, $breaks[0]->type);
        $this->assertStringContainsString('email', $breaks[0]->message);
    }

    public function testIgnoresPrivateProperties(): void
    {
        $before = new ClassInfo(
            name: 'App\\Entity',
            properties: [
                new PropertyInfo(name: 'internal', visibility: Visibility::Private),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Entity',
            properties: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
