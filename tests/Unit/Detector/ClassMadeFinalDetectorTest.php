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

use Phauthentic\BcCheck\Detector\ClassMadeFinalDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassMadeFinalDetector::class)]
final class ClassMadeFinalDetectorTest extends TestCase
{
    private ClassMadeFinalDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new ClassMadeFinalDetector();
    }

    public function testDetectsClassMadeFinal(): void
    {
        $before = new ClassInfo(name: 'App\\Service', isFinal: false);
        $after = new ClassInfo(name: 'App\\Service', isFinal: true);

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::ClassMadeFinal, $breaks[0]->type);
        $this->assertStringContainsString('final', $breaks[0]->message);
    }

    public function testNoBreakWhenAlreadyFinal(): void
    {
        $before = new ClassInfo(name: 'App\\Service', isFinal: true);
        $after = new ClassInfo(name: 'App\\Service', isFinal: true);

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenNotFinal(): void
    {
        $before = new ClassInfo(name: 'App\\Service', isFinal: false);
        $after = new ClassInfo(name: 'App\\Service', isFinal: false);

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testNoBreakWhenFinalRemoved(): void
    {
        $before = new ClassInfo(name: 'App\\Service', isFinal: true);
        $after = new ClassInfo(name: 'App\\Service', isFinal: false);

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
