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

namespace Phauthentic\BcCheck\Tests\Unit\Parser;

use Phauthentic\BcCheck\Parser\ClassAnalyzer;
use Phauthentic\BcCheck\ValueObject\ClassType;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassAnalyzer::class)]
final class ClassAnalyzerTest extends TestCase
{
    /**
     * @return list<\Phauthentic\BcCheck\ValueObject\ClassInfo>
     */
    private function analyze(string $code): array
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($code) ?? [];

        $analyzer = new ClassAnalyzer();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($analyzer);
        $traverser->traverse($ast);

        return $analyzer->getClasses();
    }

    public function testAnalyzesSimpleClass(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class SimpleClass
            {
                public string $name;
                
                public function getName(): string
                {
                    return $this->name;
                }
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertSame('App\\SimpleClass', $classes[0]->name);
        $this->assertSame(ClassType::ClassType, $classes[0]->type);
    }

    public function testAnalyzesInterface(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App\Contracts;
            
            interface ServiceInterface
            {
                public function execute(): void;
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertSame(ClassType::Interface, $classes[0]->type);
    }

    public function testAnalyzesTrait(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App\Traits;
            
            trait LoggableTrait
            {
                public function log(string $message): void
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertSame(ClassType::Trait, $classes[0]->type);
    }

    public function testAnalyzesEnum(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            enum Status: string
            {
                case Active = 'active';
                case Inactive = 'inactive';
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertSame(ClassType::Enum, $classes[0]->type);
    }

    public function testAnalyzesFinalAbstractReadonlyClass(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            final readonly class Config
            {
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertTrue($classes[0]->isFinal);
        $this->assertTrue($classes[0]->isReadonly);
    }

    public function testAnalyzesAbstractClass(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            abstract class AbstractHandler
            {
                abstract public function handle(): void;
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(1, $classes);
        $this->assertTrue($classes[0]->isAbstract);
        $this->assertTrue($classes[0]->methods[0]->isAbstract);
    }

    public function testAnalyzesClassWithParentAndInterfaces(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service extends BaseService implements ServiceInterface, LoggerAwareInterface
            {
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertSame('BaseService', $classes[0]->parentClass);
        $this->assertCount(2, $classes[0]->interfaces);
        $this->assertContains('ServiceInterface', $classes[0]->interfaces);
        $this->assertContains('LoggerAwareInterface', $classes[0]->interfaces);
    }

    public function testAnalyzesMethodWithModifiers(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                final public static function getInstance(): self
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $method = $classes[0]->methods[0];

        $this->assertTrue($method->isFinal);
        $this->assertTrue($method->isStatic);
        $this->assertSame(Visibility::Public, $method->visibility);
    }

    public function testAnalyzesMethodParameters(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                public function process(string $input, ?int $count = 10, string ...$args): void
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $params = $classes[0]->methods[0]->parameters;

        $this->assertCount(3, $params);
        $this->assertSame('input', $params[0]->name);
        $this->assertFalse($params[0]->hasDefault);
        $this->assertFalse($params[0]->isVariadic);

        $this->assertSame('count', $params[1]->name);
        $this->assertTrue($params[1]->hasDefault);
        $this->assertTrue($params[1]->type?->isNullable);

        $this->assertSame('args', $params[2]->name);
        $this->assertTrue($params[2]->isVariadic);
    }

    public function testAnalyzesParameterByReference(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                public function swap(int &$a, int &$b): void
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $params = $classes[0]->methods[0]->parameters;

        $this->assertTrue($params[0]->isByReference);
        $this->assertTrue($params[1]->isByReference);
    }

    public function testAnalyzesPromotedConstructorParameters(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class User
            {
                public function __construct(
                    public readonly string $name,
                    protected int $age = 0,
                )
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);

        // Promoted parameters should be in constructor parameters
        $method = $classes[0]->getMethod('__construct');
        $this->assertNotNull($method);
        $this->assertCount(2, $method->parameters);
        $this->assertTrue($method->parameters[0]->isPromoted);
        $this->assertTrue($method->parameters[1]->isPromoted);
    }

    public function testAnalyzesStaticProperty(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Config
            {
                public static string $version = '1.0.0';
            }
            PHP;

        $classes = $this->analyze($code);
        $prop = $classes[0]->properties[0];

        $this->assertTrue($prop->isStatic);
    }

    public function testAnalyzesConstants(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Config
            {
                public const VERSION = '1.0.0';
                protected const INTERNAL = 'internal';
                private const SECRET = 'secret';
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(3, $classes[0]->constants);

        $version = $classes[0]->getConstant('VERSION');
        $internal = $classes[0]->getConstant('INTERNAL');
        $secret = $classes[0]->getConstant('SECRET');

        $this->assertNotNull($version);
        $this->assertNotNull($internal);
        $this->assertNotNull($secret);

        $this->assertSame(Visibility::Public, $version->visibility);
        $this->assertSame(Visibility::Protected, $internal->visibility);
        $this->assertSame(Visibility::Private, $secret->visibility);
    }

    public function testAnalyzesUnionType(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                public function process(string|int $value): string|int|null
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $method = $classes[0]->methods[0];

        $this->assertTrue($method->returnType?->isUnion);
        $this->assertTrue($method->parameters[0]->type?->isUnion);
    }

    public function testAnalyzesIntersectionType(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                public function process(Countable&Traversable $value): void
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $param = $classes[0]->methods[0]->parameters[0];

        $this->assertTrue($param->type?->isIntersection);
    }

    public function testAnalyzesNullableType(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                public function find(int $id): ?User
                {
                }
            }
            PHP;

        $classes = $this->analyze($code);
        $method = $classes[0]->methods[0];

        $this->assertTrue($method->returnType?->isNullable);
    }

    public function testAnalyzesInterfaceWithExtends(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            interface ExtendedInterface extends BaseInterface, OtherInterface
            {
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(2, $classes[0]->interfaces);
    }

    public function testAnalyzesClassWithTraits(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            class Service
            {
                use LoggableTrait;
                use TimestampableTrait;
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(2, $classes[0]->traits);
    }

    public function testAnalyzesGlobalClass(): void
    {
        $code = <<<'PHP'
            <?php
            
            class GlobalClass
            {
            }
            PHP;

        $classes = $this->analyze($code);

        $this->assertSame('GlobalClass', $classes[0]->name);
    }

    public function testSkipsAnonymousClass(): void
    {
        $code = <<<'PHP'
            <?php
            namespace App;
            
            $instance = new class {
                public function handle(): void {}
            };
            PHP;

        $classes = $this->analyze($code);

        $this->assertCount(0, $classes);
    }
}
