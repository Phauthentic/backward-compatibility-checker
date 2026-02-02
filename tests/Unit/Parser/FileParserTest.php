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

use Phauthentic\BcCheck\Parser\FileParser;
use Phauthentic\BcCheck\ValueObject\ClassType;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileParser::class)]
final class FileParserTest extends TestCase
{
    private FileParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FileParser();
    }

    public function testParsesSimpleClass(): void
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

        $classes = $this->parser->parse($code);

        $this->assertCount(1, $classes);
        $this->assertSame('App\\SimpleClass', $classes[0]->name);
        $this->assertSame(ClassType::ClassType, $classes[0]->type);
        $this->assertFalse($classes[0]->isFinal);
        $this->assertCount(1, $classes[0]->properties);
        $this->assertCount(1, $classes[0]->methods);
    }

    public function testParsesFinalClass(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            final class FinalClass
            {
            }
            PHP;

        $classes = $this->parser->parse($code);

        $this->assertCount(1, $classes);
        $this->assertTrue($classes[0]->isFinal);
    }

    public function testParsesAbstractClass(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            abstract class AbstractClass
            {
                abstract public function handle(): void;
            }
            PHP;

        $classes = $this->parser->parse($code);

        $this->assertCount(1, $classes);
        $this->assertTrue($classes[0]->isAbstract);
        $this->assertTrue($classes[0]->methods[0]->isAbstract);
    }

    public function testParsesInterface(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            interface ServiceInterface
            {
                public function execute(): void;
            }
            PHP;

        $classes = $this->parser->parse($code);

        $this->assertCount(1, $classes);
        $this->assertSame(ClassType::Interface, $classes[0]->type);
    }

    public function testParsesClassWithParentAndInterfaces(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            class MyClass extends BaseClass implements FirstInterface, SecondInterface
            {
            }
            PHP;

        $classes = $this->parser->parse($code);

        $this->assertCount(1, $classes);
        $this->assertSame('BaseClass', $classes[0]->parentClass);
        $this->assertCount(2, $classes[0]->interfaces);
        $this->assertContains('FirstInterface', $classes[0]->interfaces);
        $this->assertContains('SecondInterface', $classes[0]->interfaces);
    }

    public function testParsesMethodParameters(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            class MyClass
            {
                public function handle(string $input, ?int $count = 0): void
                {
                }
            }
            PHP;

        $classes = $this->parser->parse($code);

        $method = $classes[0]->methods[0];
        $this->assertCount(2, $method->parameters);
        $this->assertSame('input', $method->parameters[0]->name);
        $this->assertSame('string', $method->parameters[0]->type?->name);
        $this->assertFalse($method->parameters[0]->hasDefault);
        $this->assertSame('count', $method->parameters[1]->name);
        $this->assertTrue($method->parameters[1]->type?->isNullable);
        $this->assertTrue($method->parameters[1]->hasDefault);
    }

    public function testParsesConstants(): void
    {
        $code = <<<'PHP'
            <?php

            namespace App;

            class MyClass
            {
                public const VERSION = '1.0.0';
                protected const INTERNAL = 'internal';
                private const SECRET = 'secret';
            }
            PHP;

        $classes = $this->parser->parse($code);

        $this->assertCount(3, $classes[0]->constants);

        $public = $classes[0]->getConstant('VERSION');
        $this->assertNotNull($public);
        $this->assertSame(Visibility::Public, $public->visibility);

        $protected = $classes[0]->getConstant('INTERNAL');
        $this->assertNotNull($protected);
        $this->assertSame(Visibility::Protected, $protected->visibility);

        $private = $classes[0]->getConstant('SECRET');
        $this->assertNotNull($private);
        $this->assertSame(Visibility::Private, $private->visibility);
    }

    public function testParsesEmptyCode(): void
    {
        $code = '<?php // empty file';

        $classes = $this->parser->parse($code);

        $this->assertCount(0, $classes);
    }
}
