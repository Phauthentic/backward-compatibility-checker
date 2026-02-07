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

namespace Phauthentic\BcCheck\Tests\Unit\ValueObject;

use Phauthentic\BcCheck\ValueObject\ParameterInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParameterInfo::class)]
final class ParameterInfoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $param = new ParameterInfo(name: 'input');

        $this->assertSame('input', $param->name);
        $this->assertNull($param->type);
        $this->assertFalse($param->hasDefault);
        $this->assertFalse($param->isVariadic);
        $this->assertFalse($param->isByReference);
        $this->assertFalse($param->isPromoted);
    }

    public function testFullyConfigured(): void
    {
        $type = new TypeInfo(name: 'string');

        $param = new ParameterInfo(
            name: 'input',
            type: $type,
            hasDefault: true,
            isVariadic: true,
            isByReference: true,
            isPromoted: true,
        );

        $this->assertSame('input', $param->name);
        $this->assertSame($type, $param->type);
        $this->assertTrue($param->hasDefault);
        $this->assertTrue($param->isVariadic);
        $this->assertTrue($param->isByReference);
        $this->assertTrue($param->isPromoted);
    }

    public function testToStringSimple(): void
    {
        $param = new ParameterInfo(name: 'input');

        $this->assertSame('$input', $param->toString());
    }

    public function testToStringWithType(): void
    {
        $param = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));

        $this->assertSame('string$input', $param->toString());
    }

    public function testToStringWithByReference(): void
    {
        $param = new ParameterInfo(name: 'input', isByReference: true);

        $this->assertStringContainsString('&', $param->toString());
    }

    public function testToStringWithVariadic(): void
    {
        $param = new ParameterInfo(name: 'args', isVariadic: true);

        $this->assertStringContainsString('...', $param->toString());
    }

    public function testToStringWithDefault(): void
    {
        $param = new ParameterInfo(name: 'input', hasDefault: true);

        $this->assertStringContainsString('= ...', $param->toString());
    }

    public function testToStringComplete(): void
    {
        $param = new ParameterInfo(
            name: 'input',
            type: new TypeInfo(name: 'string'),
            hasDefault: true,
            isByReference: true,
        );

        $result = $param->toString();

        $this->assertStringContainsString('string', $result);
        $this->assertStringContainsString('&', $result);
        $this->assertStringContainsString('$input', $result);
        $this->assertStringContainsString('= ...', $result);
    }

    public function testIsCompatibleWithNoType(): void
    {
        $param = new ParameterInfo(name: 'input', type: null);
        $other = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));

        $this->assertTrue($param->isCompatibleWith($other));
    }

    public function testIsCompatibleWithTypeRemoved(): void
    {
        $param = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));
        $other = new ParameterInfo(name: 'input', type: null);

        $this->assertFalse($param->isCompatibleWith($other));
    }

    public function testIsCompatibleWithSameType(): void
    {
        $param = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));
        $other = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));

        $this->assertTrue($param->isCompatibleWith($other));
    }

    public function testIsCompatibleWithDifferentType(): void
    {
        $param = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));
        $other = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'int'));

        $this->assertFalse($param->isCompatibleWith($other));
    }
}
