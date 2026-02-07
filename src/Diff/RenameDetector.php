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

namespace Phauthentic\BcCheck\Diff;

final readonly class RenameDetector
{
    /**
     * Regex to extract method declarations from diff lines.
     * Captures: [1] +/- prefix, [2] visibility, [3] method name
     */
    private const METHOD_PATTERN = '/^([-+])\s*'
        . '(?:final\s+)?'
        . '(?:abstract\s+)?'
        . '(public|protected|private)\s+'
        . '(?:static\s+)?'
        . 'function\s+'
        . '(\w+)\s*\(/';

    /**
     * Regex to extract property declarations from diff lines.
     * Captures: [1] +/- prefix, [2] visibility, [3] property name
     */
    private const PROPERTY_PATTERN = '/^([-+])\s*'
        . '(public|protected|private)\s+'
        . '(?:static\s+)?'
        . '(?:readonly\s+)?'
        . '(?:[?\w\\\\]+\s+)?'
        . '\$(\w+)/';

    /**
     * Detect method and property renames from a unified diff.
     *
     * @return array<string, RenameMap> Keyed by file path
     */
    public function detect(string $diff): array
    {
        $renamesByFile = [];

        foreach ($this->splitIntoFiles($diff) as $filePath => $fileDiff) {
            $methodRenames = [];
            $propertyRenames = [];

            foreach ($this->splitIntoHunks($fileDiff) as $hunk) {
                // Find method renames in this hunk
                $removedMethods = $this->extractMethods($hunk, '-');
                $addedMethods = $this->extractMethods($hunk, '+');

                foreach ($removedMethods as $removed) {
                    foreach ($addedMethods as $key => $added) {
                        // Same visibility = likely a rename
                        if ($removed['visibility'] === $added['visibility']) {
                            $methodRenames[$removed['name']] = $added['name'];
                            unset($addedMethods[$key]);
                            break;
                        }
                    }
                }

                // Find property renames in this hunk
                $removedProps = $this->extractProperties($hunk, '-');
                $addedProps = $this->extractProperties($hunk, '+');

                foreach ($removedProps as $removed) {
                    foreach ($addedProps as $key => $added) {
                        if ($removed['visibility'] === $added['visibility']) {
                            $propertyRenames[$removed['name']] = $added['name'];
                            unset($addedProps[$key]);
                            break;
                        }
                    }
                }
            }

            if ($methodRenames !== [] || $propertyRenames !== []) {
                $renamesByFile[$filePath] = new RenameMap($methodRenames, $propertyRenames);
            }
        }

        return $renamesByFile;
    }

    /**
     * Split a unified diff into per-file sections.
     *
     * @return array<string, string> Keyed by file path
     */
    private function splitIntoFiles(string $diff): array
    {
        $files = [];
        $sections = preg_split('/^diff --git /m', $diff, -1, PREG_SPLIT_NO_EMPTY);

        if ($sections === false) {
            return [];
        }

        foreach ($sections as $section) {
            // Extract file path from "a/path b/path" header
            if (preg_match('/^a\/(.+?) b\//', $section, $matches)) {
                $filePath = $matches[1];
                $files[$filePath] = $section;
            }
        }

        return $files;
    }

    /**
     * Split a file diff into hunks.
     *
     * @return list<string>
     */
    private function splitIntoHunks(string $fileDiff): array
    {
        $hunks = preg_split('/^@@[^@]+@@.*$/m', $fileDiff, -1, PREG_SPLIT_NO_EMPTY);

        if ($hunks === false) {
            return [];
        }

        // Skip the first section (file header before first hunk)
        return array_slice($hunks, 1);
    }

    /**
     * Extract method declarations from a hunk.
     *
     * @return list<array{name: string, visibility: string}>
     */
    private function extractMethods(string $hunk, string $prefix): array
    {
        $methods = [];

        foreach (explode("\n", $hunk) as $line) {
            if (preg_match(self::METHOD_PATTERN, $line, $matches)) {
                if ($matches[1] === $prefix) {
                    $methods[] = [
                        'name' => $matches[3],
                        'visibility' => $matches[2],
                    ];
                }
            }
        }

        return $methods;
    }

    /**
     * Extract property declarations from a hunk.
     *
     * @return list<array{name: string, visibility: string}>
     */
    private function extractProperties(string $hunk, string $prefix): array
    {
        $properties = [];

        foreach (explode("\n", $hunk) as $line) {
            if (preg_match(self::PROPERTY_PATTERN, $line, $matches)) {
                if ($matches[1] === $prefix) {
                    $properties[] = [
                        'name' => $matches[3],
                        'visibility' => $matches[2],
                    ];
                }
            }
        }

        return $properties;
    }
}
