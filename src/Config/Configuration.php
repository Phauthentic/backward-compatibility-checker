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

namespace Phauthentic\BcCheck\Config;

final readonly class Configuration implements ConfigurationInterface
{
    /**
     * @param list<string> $includePatterns
     * @param list<string> $excludePatterns
     * @param list<string> $sourceDirectories
     * @param list<class-string> $externalDetectors
     */
    public function __construct(
        private array $includePatterns = [],
        private array $excludePatterns = [],
        private array $sourceDirectories = ['src/'],
        private array $externalDetectors = [],
    ) {
    }

    public function getIncludePatterns(): array
    {
        return $this->includePatterns;
    }

    public function getExcludePatterns(): array
    {
        return $this->excludePatterns;
    }

    public function getSourceDirectories(): array
    {
        return $this->sourceDirectories;
    }

    public function getExternalDetectors(): array
    {
        return $this->externalDetectors;
    }

    public function shouldInclude(string $fqcn): bool
    {
        // Check exclude patterns first
        foreach ($this->excludePatterns as $pattern) {
            if ($this->matchesPattern($fqcn, $pattern)) {
                return false;
            }
        }

        // If no include patterns, include everything not excluded
        if ($this->includePatterns === []) {
            return true;
        }

        // Check include patterns
        foreach ($this->includePatterns as $pattern) {
            if ($this->matchesPattern($fqcn, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPattern(string $fqcn, string $pattern): bool
    {
        // Ensure the pattern is a valid regex
        $regex = '/' . $pattern . '/';

        // Suppress warnings for invalid patterns
        $result = @preg_match($regex, $fqcn);

        return $result === 1;
    }
}
