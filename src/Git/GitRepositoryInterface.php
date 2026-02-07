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

namespace Phauthentic\BcCheck\Git;

interface GitRepositoryInterface
{
    /**
     * Get the contents of a file at a specific commit.
     */
    public function getFileContentAtCommit(string $commitHash, string $filePath): string;

    /**
     * Get list of PHP files at a specific commit.
     *
     * @param list<string> $directories Directories to search in (relative to repo root)
     * @return list<string> List of file paths
     */
    public function getPhpFilesAtCommit(string $commitHash, array $directories): array;

    /**
     * Check if a commit hash is valid.
     */
    public function isValidCommit(string $commitHash): bool;

    /**
     * Get the repository root path.
     */
    public function getRepositoryPath(): string;

    /**
     * Get the unified diff between two commits.
     */
    public function getDiff(string $fromCommit, string $toCommit): string;
}
