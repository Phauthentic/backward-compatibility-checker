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

use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyInfo::class)]
final class PropertyInfoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $property = new PropertyInfo(name: 'name', visibility: Visibility::Public);

        $this->assertSame('name', $property->name);
        $this->assertSame(Visibility::Public, $property->visibility);
        $this->assertNull($property->type);
        $this->assertFalse($property->isStatic);
        $this->assertFalse($property->isReadonly);
        $this->assertFalse($property->hasDefault);
    }

    public function testFullyConfigured(): void
    {
        $type = new TypeInfo(name: 'string');

        $property = new PropertyInfo(
            name: 'name',
            visibility: Visibility::Protected,
            type: $type,
            isStatic: true,
            isReadonly: true,
            hasDefault: true,
        );

        $this->assertSame('name', $property->name);
        $this->assertSame(Visibility::Protected, $property->visibility);
        $this->assertSame($type, $property->type);
        $this->assertTrue($property->isStatic);
        $this->assertTrue($property->isReadonly);
        $this->assertTrue($property->hasDefault);
    }

    public function testGetSignatureSimple(): void
    {
        $property = new PropertyInfo(name: 'name', visibility: Visibility::Public);

        $signature = $property->getSignature();

        $this->assertStringContainsString('public', $signature);
        $this->assertStringContainsString('$name', $signature);
    }

    public function testGetSignatureWithReadonly(): void
    {
        $property = new PropertyInfo(
            name: 'name',
            visibility: Visibility::Public,
            isReadonly: true,
        );

        $signature = $property->getSignature();

        $this->assertStringContainsString('readonly', $signature);
    }

    public function testGetSignatureWithStatic(): void
    {
        $property = new PropertyInfo(
            name: 'count',
            visibility: Visibility::Public,
            isStatic: true,
        );

        $signature = $property->getSignature();

        $this->assertStringContainsString('static', $signature);
    }

    public function testGetSignatureWithType(): void
    {
        $property = new PropertyInfo(
            name: 'name',
            visibility: Visibility::Public,
            type: new TypeInfo(name: 'string'),
        );

        $signature = $property->getSignature();

        $this->assertStringContainsString('string', $signature);
    }

    public function testGetSignatureComplete(): void
    {
        $property = new PropertyInfo(
            name: 'name',
            visibility: Visibility::Protected,
            type: new TypeInfo(name: 'string'),
            isStatic: true,
            isReadonly: true,
        );

        $signature = $property->getSignature();

        $this->assertSame('readonly protected static string $name', $signature);
    }
}
