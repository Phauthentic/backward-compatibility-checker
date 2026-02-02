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

namespace Phauthentic\BcCheck\Tests\Unit\Config;

use Phauthentic\BcCheck\Config\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = new Configuration();

        $this->assertSame([], $config->getIncludePatterns());
        $this->assertSame([], $config->getExcludePatterns());
        $this->assertSame(['src/'], $config->getSourceDirectories());
    }

    public function testCustomConfiguration(): void
    {
        $config = new Configuration(
            includePatterns: ['^App\\\\.*'],
            excludePatterns: ['.*Test$'],
            sourceDirectories: ['src/', 'lib/'],
        );

        $this->assertSame(['^App\\\\.*'], $config->getIncludePatterns());
        $this->assertSame(['.*Test$'], $config->getExcludePatterns());
        $this->assertSame(['src/', 'lib/'], $config->getSourceDirectories());
    }

    /**
     * @param list<string> $include
     * @param list<string> $exclude
     */
    #[DataProvider('provideShouldIncludeCases')]
    public function testShouldInclude(
        array $include,
        array $exclude,
        string $fqcn,
        bool $expected,
    ): void {
        $config = new Configuration(
            includePatterns: $include,
            excludePatterns: $exclude,
        );

        $this->assertSame($expected, $config->shouldInclude($fqcn));
    }

    /**
     * @return iterable<string, array{array<string>, array<string>, string, bool}>
     */
    public static function provideShouldIncludeCases(): iterable
    {
        yield 'no patterns includes everything' => [
            [],
            [],
            'App\\Service\\UserService',
            true,
        ];

        yield 'include pattern matches' => [
            ['^App\\\\Service\\\\.*'],
            [],
            'App\\Service\\UserService',
            true,
        ];

        yield 'include pattern does not match' => [
            ['^App\\\\Controller\\\\.*'],
            [],
            'App\\Service\\UserService',
            false,
        ];

        yield 'exclude pattern matches' => [
            [],
            ['.*Test$'],
            'App\\Service\\UserServiceTest',
            false,
        ];

        yield 'exclude takes precedence over include' => [
            ['^App\\\\.*'],
            ['.*Test$'],
            'App\\Service\\UserServiceTest',
            false,
        ];

        yield 'internal namespace excluded' => [
            [],
            ['.*\\\\Internal\\\\.*'],
            'App\\Internal\\Helper',
            false,
        ];
    }
}
