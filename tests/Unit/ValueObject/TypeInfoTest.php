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

namespace Phauthentic\BcCheck\Tests\Unit\ValueObject;

use Phauthentic\BcCheck\ValueObject\TypeInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeInfo::class)]
final class TypeInfoTest extends TestCase
{
    public function testSimpleType(): void
    {
        $type = new TypeInfo('string');

        $this->assertSame('string', $type->toString());
        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isUnion);
        $this->assertFalse($type->isIntersection);
    }

    public function testNullableType(): void
    {
        $type = new TypeInfo('string', isNullable: true);

        $this->assertSame('?string', $type->toString());
        $this->assertTrue($type->isNullable);
    }

    public function testUnionType(): void
    {
        $type = new TypeInfo('string|int', isUnion: true, types: ['string', 'int']);

        $this->assertSame('string|int', $type->toString());
        $this->assertTrue($type->isUnion);
    }

    public function testIntersectionType(): void
    {
        $type = new TypeInfo('Countable&Iterator', isIntersection: true, types: ['Countable', 'Iterator']);

        $this->assertSame('Countable&Iterator', $type->toString());
        $this->assertTrue($type->isIntersection);
    }

    public function testEquals(): void
    {
        $type1 = new TypeInfo('string');
        $type2 = new TypeInfo('string');
        $type3 = new TypeInfo('int');

        $this->assertTrue($type1->equals($type2));
        $this->assertFalse($type1->equals($type3));
    }

    public function testFromString(): void
    {
        $simple = TypeInfo::fromString('string');
        $this->assertSame('string', $simple->name);

        $nullable = TypeInfo::fromString('?int');
        $this->assertTrue($nullable->isNullable);
        $this->assertSame('int', $nullable->name);

        $union = TypeInfo::fromString('string|int');
        $this->assertTrue($union->isUnion);
        $this->assertSame(['string', 'int'], $union->types);
    }

    public function testFromStringEmpty(): void
    {
        $type = TypeInfo::fromString('');

        $this->assertSame('mixed', $type->name);
    }

    public function testFromStringIntersection(): void
    {
        $type = TypeInfo::fromString('Countable&Traversable');

        $this->assertTrue($type->isIntersection);
        $this->assertSame(['Countable', 'Traversable'], $type->types);
    }

    public function testFromStringUnionWithNull(): void
    {
        $type = TypeInfo::fromString('string|null');

        $this->assertTrue($type->isUnion);
        $this->assertTrue($type->isNullable);
    }

    public function testToStringMixedNotPrefixed(): void
    {
        $type = new TypeInfo('mixed', isNullable: true);

        $this->assertSame('mixed', $type->toString());
    }

    public function testToStringNullNotPrefixed(): void
    {
        $type = new TypeInfo('null', isNullable: true);

        $this->assertSame('null', $type->toString());
    }
}
