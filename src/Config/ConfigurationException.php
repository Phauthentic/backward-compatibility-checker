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

namespace Phauthentic\BcCheck\Config;

use Phauthentic\BcCheck\Exception\BcCheckException;
use Throwable;

final class ConfigurationException extends BcCheckException
{
    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('Configuration file not found: %s', $path));
    }

    public static function fileNotReadable(string $path): self
    {
        return new self(sprintf('Could not read configuration file: %s', $path));
    }

    public static function invalidYaml(string $message, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Invalid YAML in configuration file: %s', $message),
            $previous,
        );
    }

    public static function keyMustBeArray(string $key): self
    {
        return new self(sprintf('Configuration key "%s" must be an array', $key));
    }

    public static function itemsMustBeStrings(string $key): self
    {
        return new self(sprintf('All items in "%s" must be strings', $key));
    }

    public static function invalidRegexPattern(string $pattern, string $context): self
    {
        return new self(sprintf('Invalid regex pattern in %s: %s', $context, $pattern));
    }

    public static function externalDetectorNotFound(string $class): self
    {
        return new self(sprintf('External detector class not found: %s', $class));
    }

    public static function externalDetectorInvalidInterface(string $class, string $expectedInterface): self
    {
        return new self(sprintf(
            'External detector class must implement %s: %s',
            $expectedInterface,
            $class,
        ));
    }
}
