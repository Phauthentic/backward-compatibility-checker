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

use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\ParameterInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodInfo::class)]
final class MethodInfoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $method = new MethodInfo(name: 'handle', visibility: Visibility::Public);

        $this->assertSame('handle', $method->name);
        $this->assertSame(Visibility::Public, $method->visibility);
        $this->assertFalse($method->isStatic);
        $this->assertFalse($method->isFinal);
        $this->assertFalse($method->isAbstract);
        $this->assertNull($method->returnType);
        $this->assertSame([], $method->parameters);
    }

    public function testFullyConfigured(): void
    {
        $param = new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string'));
        $returnType = new TypeInfo(name: 'void');

        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Protected,
            isStatic: true,
            isFinal: true,
            isAbstract: false,
            returnType: $returnType,
            parameters: [$param],
        );

        $this->assertSame('handle', $method->name);
        $this->assertSame(Visibility::Protected, $method->visibility);
        $this->assertTrue($method->isStatic);
        $this->assertTrue($method->isFinal);
        $this->assertFalse($method->isAbstract);
        $this->assertSame($returnType, $method->returnType);
        $this->assertCount(1, $method->parameters);
    }

    public function testGetSignatureSimple(): void
    {
        $method = new MethodInfo(name: 'handle', visibility: Visibility::Public);

        $signature = $method->getSignature();

        $this->assertStringContainsString('public', $signature);
        $this->assertStringContainsString('function', $signature);
        $this->assertStringContainsString('handle', $signature);
    }

    public function testGetSignatureWithModifiers(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Protected,
            isStatic: true,
            isFinal: true,
        );

        $signature = $method->getSignature();

        $this->assertStringContainsString('final', $signature);
        $this->assertStringContainsString('protected', $signature);
        $this->assertStringContainsString('static', $signature);
    }

    public function testGetSignatureWithAbstract(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Public,
            isAbstract: true,
        );

        $signature = $method->getSignature();

        $this->assertStringContainsString('abstract', $signature);
    }

    public function testGetSignatureWithReturnType(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Public,
            returnType: new TypeInfo(name: 'string'),
        );

        $signature = $method->getSignature();

        $this->assertStringContainsString(': string', $signature);
    }

    public function testGetSignatureWithParameters(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Public,
            parameters: [
                new ParameterInfo(name: 'input', type: new TypeInfo(name: 'string')),
                new ParameterInfo(name: 'count', type: new TypeInfo(name: 'int')),
            ],
        );

        $signature = $method->getSignature();

        $this->assertStringContainsString('string$input', $signature);
        $this->assertStringContainsString('int$count', $signature);
    }

    public function testGetRequiredParameterCount(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Public,
            parameters: [
                new ParameterInfo(name: 'required1', hasDefault: false),
                new ParameterInfo(name: 'required2', hasDefault: false),
                new ParameterInfo(name: 'optional', hasDefault: true),
                new ParameterInfo(name: 'variadic', isVariadic: true),
            ],
        );

        $this->assertSame(2, $method->getRequiredParameterCount());
    }

    public function testGetRequiredParameterCountWithNoRequired(): void
    {
        $method = new MethodInfo(
            name: 'handle',
            visibility: Visibility::Public,
            parameters: [
                new ParameterInfo(name: 'optional', hasDefault: true),
            ],
        );

        $this->assertSame(0, $method->getRequiredParameterCount());
    }
}
