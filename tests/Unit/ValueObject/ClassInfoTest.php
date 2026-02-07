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

use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\ClassType;
use Phauthentic\BcCheck\ValueObject\ConstantInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassInfo::class)]
final class ClassInfoTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $classInfo = new ClassInfo(name: 'App\\Service');

        $this->assertSame('App\\Service', $classInfo->name);
        $this->assertSame(ClassType::ClassType, $classInfo->type);
        $this->assertFalse($classInfo->isFinal);
        $this->assertFalse($classInfo->isAbstract);
        $this->assertFalse($classInfo->isReadonly);
        $this->assertNull($classInfo->parentClass);
        $this->assertSame([], $classInfo->interfaces);
        $this->assertSame([], $classInfo->traits);
        $this->assertSame([], $classInfo->methods);
        $this->assertSame([], $classInfo->properties);
        $this->assertSame([], $classInfo->constants);
    }

    public function testGetMethodReturnsMethod(): void
    {
        $method = new MethodInfo(name: 'handle', visibility: Visibility::Public);
        $classInfo = new ClassInfo(name: 'App\\Service', methods: [$method]);

        $result = $classInfo->getMethod('handle');

        $this->assertSame($method, $result);
    }

    public function testGetMethodReturnsNullWhenNotFound(): void
    {
        $classInfo = new ClassInfo(name: 'App\\Service', methods: []);

        $result = $classInfo->getMethod('nonexistent');

        $this->assertNull($result);
    }

    public function testGetPropertyReturnsProperty(): void
    {
        $property = new PropertyInfo(name: 'name', visibility: Visibility::Public);
        $classInfo = new ClassInfo(name: 'App\\Entity', properties: [$property]);

        $result = $classInfo->getProperty('name');

        $this->assertSame($property, $result);
    }

    public function testGetPropertyReturnsNullWhenNotFound(): void
    {
        $classInfo = new ClassInfo(name: 'App\\Entity', properties: []);

        $result = $classInfo->getProperty('nonexistent');

        $this->assertNull($result);
    }

    public function testGetConstantReturnsConstant(): void
    {
        $constant = new ConstantInfo(name: 'VERSION', visibility: Visibility::Public);
        $classInfo = new ClassInfo(name: 'App\\Config', constants: [$constant]);

        $result = $classInfo->getConstant('VERSION');

        $this->assertSame($constant, $result);
    }

    public function testGetConstantReturnsNullWhenNotFound(): void
    {
        $classInfo = new ClassInfo(name: 'App\\Config', constants: []);

        $result = $classInfo->getConstant('nonexistent');

        $this->assertNull($result);
    }

    public function testHasInterface(): void
    {
        $classInfo = new ClassInfo(
            name: 'App\\Service',
            interfaces: ['App\\Contracts\\ServiceInterface', 'App\\Contracts\\LoggerInterface'],
        );

        $this->assertTrue($classInfo->hasInterface('App\\Contracts\\ServiceInterface'));
        $this->assertTrue($classInfo->hasInterface('App\\Contracts\\LoggerInterface'));
        $this->assertFalse($classInfo->hasInterface('App\\Contracts\\OtherInterface'));
    }

    public function testGetPublicMethods(): void
    {
        $publicMethod = new MethodInfo(name: 'public', visibility: Visibility::Public);
        $protectedMethod = new MethodInfo(name: 'protected', visibility: Visibility::Protected);
        $privateMethod = new MethodInfo(name: 'private', visibility: Visibility::Private);

        $classInfo = new ClassInfo(
            name: 'App\\Service',
            methods: [$publicMethod, $protectedMethod, $privateMethod],
        );

        $result = $classInfo->getPublicMethods();

        $this->assertCount(1, $result);
        $this->assertSame($publicMethod, $result[0]);
    }

    public function testGetPublicOrProtectedMethods(): void
    {
        $publicMethod = new MethodInfo(name: 'public', visibility: Visibility::Public);
        $protectedMethod = new MethodInfo(name: 'protected', visibility: Visibility::Protected);
        $privateMethod = new MethodInfo(name: 'private', visibility: Visibility::Private);

        $classInfo = new ClassInfo(
            name: 'App\\Service',
            methods: [$publicMethod, $protectedMethod, $privateMethod],
        );

        $result = $classInfo->getPublicOrProtectedMethods();

        $this->assertCount(2, $result);
    }

    public function testGetPublicOrProtectedProperties(): void
    {
        $publicProp = new PropertyInfo(name: 'public', visibility: Visibility::Public);
        $protectedProp = new PropertyInfo(name: 'protected', visibility: Visibility::Protected);
        $privateProp = new PropertyInfo(name: 'private', visibility: Visibility::Private);

        $classInfo = new ClassInfo(
            name: 'App\\Entity',
            properties: [$publicProp, $protectedProp, $privateProp],
        );

        $result = $classInfo->getPublicOrProtectedProperties();

        $this->assertCount(2, $result);
    }

    public function testGetPublicOrProtectedConstants(): void
    {
        $publicConst = new ConstantInfo(name: 'PUBLIC', visibility: Visibility::Public);
        $protectedConst = new ConstantInfo(name: 'PROTECTED', visibility: Visibility::Protected);
        $privateConst = new ConstantInfo(name: 'PRIVATE', visibility: Visibility::Private);

        $classInfo = new ClassInfo(
            name: 'App\\Config',
            constants: [$publicConst, $protectedConst, $privateConst],
        );

        $result = $classInfo->getPublicOrProtectedConstants();

        $this->assertCount(2, $result);
    }
}
