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
use Phauthentic\BcCheck\Exception\BcCheckException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ConfigurationException::class)]
final class ConfigurationExceptionTest extends TestCase
{
    public function testExtendsBaseException(): void
    {
        $exception = ConfigurationException::fileNotFound('/path');

        $this->assertInstanceOf(BcCheckException::class, $exception);
    }

    public function testFileNotFound(): void
    {
        $exception = ConfigurationException::fileNotFound('/path/to/config.yaml');

        $this->assertSame('Configuration file not found: /path/to/config.yaml', $exception->getMessage());
    }

    public function testFileNotReadable(): void
    {
        $exception = ConfigurationException::fileNotReadable('/path/to/config.yaml');

        $this->assertSame('Could not read configuration file: /path/to/config.yaml', $exception->getMessage());
    }

    public function testInvalidYaml(): void
    {
        $exception = ConfigurationException::invalidYaml('Syntax error');

        $this->assertSame('Invalid YAML in configuration file: Syntax error', $exception->getMessage());
    }

    public function testInvalidYamlWithPrevious(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = ConfigurationException::invalidYaml('Syntax error', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testKeyMustBeArray(): void
    {
        $exception = ConfigurationException::keyMustBeArray('include');

        $this->assertSame('Configuration key "include" must be an array', $exception->getMessage());
    }

    public function testItemsMustBeStrings(): void
    {
        $exception = ConfigurationException::itemsMustBeStrings('include');

        $this->assertSame('All items in "include" must be strings', $exception->getMessage());
    }

    public function testInvalidRegexPattern(): void
    {
        $exception = ConfigurationException::invalidRegexPattern('[invalid', 'include');

        $this->assertSame('Invalid regex pattern in include: [invalid', $exception->getMessage());
    }

    public function testExternalDetectorNotFound(): void
    {
        $exception = ConfigurationException::externalDetectorNotFound('NonExistentClass');

        $this->assertSame('External detector class not found: NonExistentClass', $exception->getMessage());
    }

    public function testExternalDetectorInvalidInterface(): void
    {
        $exception = ConfigurationException::externalDetectorInvalidInterface(
            'MyClass',
            'SomeInterface',
        );

        $this->assertSame('External detector class must implement SomeInterface: MyClass', $exception->getMessage());
    }
}
