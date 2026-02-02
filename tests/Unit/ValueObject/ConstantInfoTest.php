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

use Phauthentic\BcCheck\ValueObject\ConstantInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConstantInfo::class)]
final class ConstantInfoTest extends TestCase
{
    public function testConstructor(): void
    {
        $constant = new ConstantInfo(name: 'VERSION', visibility: Visibility::Public);

        $this->assertSame('VERSION', $constant->name);
        $this->assertSame(Visibility::Public, $constant->visibility);
        $this->assertFalse($constant->isFinal);
        $this->assertNull($constant->type);
    }

    public function testWithDifferentVisibilities(): void
    {
        $public = new ConstantInfo(name: 'PUBLIC', visibility: Visibility::Public);
        $protected = new ConstantInfo(name: 'PROTECTED', visibility: Visibility::Protected);
        $private = new ConstantInfo(name: 'PRIVATE', visibility: Visibility::Private);

        $this->assertSame(Visibility::Public, $public->visibility);
        $this->assertSame(Visibility::Protected, $protected->visibility);
        $this->assertSame(Visibility::Private, $private->visibility);
    }

    public function testGetSignatureSimple(): void
    {
        $constant = new ConstantInfo(name: 'VERSION', visibility: Visibility::Public);

        $signature = $constant->getSignature();

        $this->assertStringContainsString('public', $signature);
        $this->assertStringContainsString('const', $signature);
        $this->assertStringContainsString('VERSION', $signature);
    }

    public function testGetSignatureWithFinal(): void
    {
        $constant = new ConstantInfo(
            name: 'VERSION',
            visibility: Visibility::Public,
            isFinal: true,
        );

        $signature = $constant->getSignature();

        $this->assertStringContainsString('final', $signature);
    }

    public function testGetSignatureWithType(): void
    {
        $constant = new ConstantInfo(
            name: 'VERSION',
            visibility: Visibility::Public,
            type: new TypeInfo(name: 'string'),
        );

        $signature = $constant->getSignature();

        $this->assertStringContainsString('string', $signature);
    }

    public function testGetSignatureComplete(): void
    {
        $constant = new ConstantInfo(
            name: 'VERSION',
            visibility: Visibility::Protected,
            isFinal: true,
            type: new TypeInfo(name: 'string'),
        );

        $signature = $constant->getSignature();

        $this->assertSame('final protected const string VERSION', $signature);
    }
}
