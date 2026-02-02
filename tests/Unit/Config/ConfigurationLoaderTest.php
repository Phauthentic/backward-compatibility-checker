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

use Phauthentic\BcCheck\Config\ConfigurationException;
use Phauthentic\BcCheck\Config\ConfigurationLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationLoader::class)]
final class ConfigurationLoaderTest extends TestCase
{
    private ConfigurationLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new ConfigurationLoader();
    }

    public function testCreateFromArrayWithValidData(): void
    {
        $data = [
            'include' => ['^App\\\\.*'],
            'exclude' => ['.*Test$'],
            'source_directories' => ['src/', 'lib/'],
        ];

        $config = $this->loader->createFromArray($data);

        $this->assertSame(['^App\\\\.*'], $config->getIncludePatterns());
        $this->assertSame(['.*Test$'], $config->getExcludePatterns());
        $this->assertSame(['src/', 'lib/'], $config->getSourceDirectories());
    }

    public function testCreateFromArrayWithEmptyData(): void
    {
        $config = $this->loader->createFromArray([]);

        $this->assertSame([], $config->getIncludePatterns());
        $this->assertSame([], $config->getExcludePatterns());
        $this->assertSame(['src/'], $config->getSourceDirectories());
    }

    public function testCreateDefault(): void
    {
        $config = $this->loader->createDefault();

        $this->assertSame([], $config->getIncludePatterns());
        $this->assertSame([], $config->getExcludePatterns());
        $this->assertSame(['src/'], $config->getSourceDirectories());
    }

    public function testThrowsOnInvalidIncludeType(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must be an array');

        $this->loader->createFromArray(['include' => 'not-an-array']);
    }

    public function testThrowsOnInvalidPatternItem(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must be strings');

        $this->loader->createFromArray(['include' => [123]]);
    }

    public function testThrowsOnInvalidRegex(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Invalid regex');

        $this->loader->createFromArray(['include' => ['[invalid']]);
    }

    public function testThrowsOnMissingFile(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('not found');

        $this->loader->load('/nonexistent/path/config.yaml');
    }
}
