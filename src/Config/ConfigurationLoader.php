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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class ConfigurationLoader
{
    public function load(string $path): Configuration
    {
        if (!file_exists($path)) {
            throw ConfigurationException::fileNotFound($path);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw ConfigurationException::fileNotReadable($path);
        }

        try {
            $parsed = Yaml::parse($content);
        } catch (ParseException $e) {
            throw ConfigurationException::invalidYaml($e->getMessage(), $e);
        }

        /** @var array<string, mixed> $data */
        $data = is_array($parsed) ? $parsed : [];

        return $this->createFromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromArray(array $data): Configuration
    {
        $includePatterns = $this->extractStringList($data, 'include');
        $excludePatterns = $this->extractStringList($data, 'exclude');
        $sourceDirectories = $this->extractStringList($data, 'source_directories');
        $externalDetectors = $this->extractStringList($data, 'external_detectors');

        // Validate regex patterns
        foreach ($includePatterns as $pattern) {
            $this->validatePattern($pattern, 'include');
        }

        foreach ($excludePatterns as $pattern) {
            $this->validatePattern($pattern, 'exclude');
        }

        /** @var list<class-string> $externalDetectorClasses */
        $externalDetectorClasses = $externalDetectors;

        return new Configuration(
            includePatterns: $includePatterns,
            excludePatterns: $excludePatterns,
            sourceDirectories: $sourceDirectories !== [] ? $sourceDirectories : ['src/'],
            externalDetectors: $externalDetectorClasses,
        );
    }

    public function createDefault(): Configuration
    {
        return new Configuration();
    }

    /**
     * @param array<string, mixed> $data
     * @return list<string>
     */
    private function extractStringList(array $data, string $key): array
    {
        if (!isset($data[$key])) {
            return [];
        }

        if (!is_array($data[$key])) {
            throw ConfigurationException::keyMustBeArray($key);
        }

        $result = [];
        foreach ($data[$key] as $item) {
            if (!is_string($item)) {
                throw ConfigurationException::itemsMustBeStrings($key);
            }
            $result[] = $item;
        }

        return $result;
    }

    private function validatePattern(string $pattern, string $context): void
    {
        $regex = '/' . $pattern . '/';

        // Use error suppression and check result
        if (@preg_match($regex, '') === false) {
            throw ConfigurationException::invalidRegexPattern($pattern, $context);
        }
    }
}
