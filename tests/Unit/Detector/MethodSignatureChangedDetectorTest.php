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

use Phauthentic\BcCheck\Detector\MethodSignatureChangedDetector;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\ParameterInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodSignatureChangedDetector::class)]
final class MethodSignatureChangedDetectorTest extends TestCase
{
    private MethodSignatureChangedDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new MethodSignatureChangedDetector();
    }

    public function testDetectsAddedRequiredParameter(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input'),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input'),
                        new ParameterInfo(name: 'output'),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::MethodSignatureChanged, $breaks[0]->type);
        $this->assertStringContainsString('required parameters', $breaks[0]->message);
    }

    public function testDetectsParameterTypeChanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: new TypeInfo('string')),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: new TypeInfo('int')),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('changed type from', $breaks[0]->message);
    }

    public function testDetectsParameterMadeRequired(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', hasDefault: true),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', hasDefault: false),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        // Removing default can trigger multiple detections (type added + no longer optional)
        $this->assertGreaterThanOrEqual(1, count($breaks));
        $optionalBreaks = array_filter($breaks, fn ($b) => str_contains($b->message, 'no longer optional'));
        $this->assertCount(1, $optionalBreaks);
    }

    public function testNoBreakWhenSignatureUnchanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: new TypeInfo('string')),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: new TypeInfo('string')),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }

    public function testDetectsParameterTypeAdded(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: null),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', type: new TypeInfo('string')),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('now has type', $breaks[0]->message);
    }

    public function testDetectsByReferenceChanged(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', isByReference: false),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', isByReference: true),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('by reference', $breaks[0]->message);
    }

    public function testDetectsByReferenceRemoved(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', isByReference: true),
                    ],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [
                        new ParameterInfo(name: 'input', isByReference: false),
                    ],
                ),
            ],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('no longer passed by reference', $breaks[0]->message);
    }

    public function testSkipsRemovedMethods(): void
    {
        $before = new ClassInfo(
            name: 'App\\Service',
            methods: [
                new MethodInfo(
                    name: 'handle',
                    visibility: Visibility::Public,
                    parameters: [new ParameterInfo(name: 'input')],
                ),
            ],
        );

        $after = new ClassInfo(
            name: 'App\\Service',
            methods: [],
        );

        $breaks = $this->detector->detect($before, $after);

        $this->assertCount(0, $breaks);
    }
}
