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

use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BcBreak::class)]
final class BcBreakTest extends TestCase
{
    public function testConstructor(): void
    {
        $break = new BcBreak(
            message: 'Method was removed',
            className: 'App\\Service',
            memberName: 'handle',
            type: BcBreakType::MethodRemoved,
        );

        $this->assertSame('Method was removed', $break->message);
        $this->assertSame('App\\Service', $break->className);
        $this->assertSame('handle', $break->memberName);
        $this->assertSame(BcBreakType::MethodRemoved, $break->type);
    }

    public function testDefaultValues(): void
    {
        $break = new BcBreak(
            message: 'Some break',
            className: 'App\\Service',
        );

        $this->assertNull($break->memberName);
        $this->assertSame(BcBreakType::Other, $break->type);
    }

    public function testGetFullIdentifierWithMember(): void
    {
        $break = new BcBreak(
            message: 'Method was removed',
            className: 'App\\Service',
            memberName: 'handle',
        );

        $this->assertSame('App\\Service::handle', $break->getFullIdentifier());
    }

    public function testGetFullIdentifierWithoutMember(): void
    {
        $break = new BcBreak(
            message: 'Class was made final',
            className: 'App\\Service',
        );

        $this->assertSame('App\\Service', $break->getFullIdentifier());
    }

    public function testToString(): void
    {
        $break = new BcBreak(
            message: 'Method was removed',
            className: 'App\\Service',
            type: BcBreakType::MethodRemoved,
        );

        $result = $break->toString();

        $this->assertStringContainsString('[BC]', $result);
        $this->assertStringContainsString('METHOD_REMOVED', $result);
        $this->assertStringContainsString('Method was removed', $result);
    }
}
