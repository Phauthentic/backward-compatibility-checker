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

use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Visibility::class)]
final class VisibilityTest extends TestCase
{
    #[DataProvider('provideMoreRestrictiveCases')]
    public function testIsMoreRestrictiveThan(Visibility $current, Visibility $other, bool $expected): void
    {
        $this->assertSame($expected, $current->isMoreRestrictiveThan($other));
    }

    /**
     * @return iterable<string, array{Visibility, Visibility, bool}>
     */
    public static function provideMoreRestrictiveCases(): iterable
    {
        yield 'private is more restrictive than protected' => [
            Visibility::Private,
            Visibility::Protected,
            true,
        ];

        yield 'private is more restrictive than public' => [
            Visibility::Private,
            Visibility::Public,
            true,
        ];

        yield 'protected is more restrictive than public' => [
            Visibility::Protected,
            Visibility::Public,
            true,
        ];

        yield 'public is not more restrictive than protected' => [
            Visibility::Public,
            Visibility::Protected,
            false,
        ];

        yield 'public is not more restrictive than private' => [
            Visibility::Public,
            Visibility::Private,
            false,
        ];

        yield 'protected is not more restrictive than private' => [
            Visibility::Protected,
            Visibility::Private,
            false,
        ];

        yield 'same visibility is not more restrictive' => [
            Visibility::Public,
            Visibility::Public,
            false,
        ];
    }

    public function testIsAccessibleFromPublic(): void
    {
        $this->assertTrue(Visibility::Public->isAccessibleFrom(Visibility::Public));
        $this->assertTrue(Visibility::Public->isAccessibleFrom(Visibility::Protected));
        $this->assertTrue(Visibility::Public->isAccessibleFrom(Visibility::Private));
    }

    public function testIsAccessibleFromProtected(): void
    {
        $this->assertFalse(Visibility::Protected->isAccessibleFrom(Visibility::Public));
        $this->assertTrue(Visibility::Protected->isAccessibleFrom(Visibility::Protected));
        $this->assertTrue(Visibility::Protected->isAccessibleFrom(Visibility::Private));
    }

    public function testIsAccessibleFromPrivate(): void
    {
        $this->assertFalse(Visibility::Private->isAccessibleFrom(Visibility::Public));
        $this->assertFalse(Visibility::Private->isAccessibleFrom(Visibility::Protected));
        $this->assertTrue(Visibility::Private->isAccessibleFrom(Visibility::Private));
    }
}
