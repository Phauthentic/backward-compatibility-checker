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

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final readonly class GitRepository implements GitRepositoryInterface
{
    public function __construct(
        private string $repositoryPath,
    ) {
        if (!is_dir($this->repositoryPath)) {
            throw GitException::repositoryPathNotFound($this->repositoryPath);
        }

        if (!is_dir($this->repositoryPath . '/.git')) {
            throw GitException::notAGitRepository($this->repositoryPath);
        }
    }

    public function getFileContentAtCommit(string $commitHash, string $filePath): string
    {
        $process = $this->runGit(['show', sprintf('%s:%s', $commitHash, $filePath)]);

        return $process->getOutput();
    }

    public function getPhpFilesAtCommit(string $commitHash, array $directories): array
    {
        if ($directories === []) {
            $directories = ['.'];
        }

        $files = [];

        foreach ($directories as $directory) {
            $directory = rtrim($directory, '/');

            try {
                $process = $this->runGit([
                    'ls-tree',
                    '-r',
                    '--name-only',
                    $commitHash,
                    '--',
                    $directory,
                ]);

                $output = trim($process->getOutput());
                if ($output === '') {
                    continue;
                }

                $allFiles = explode("\n", $output);

                foreach ($allFiles as $file) {
                    if (str_ends_with($file, '.php')) {
                        $files[] = $file;
                    }
                }
            } catch (GitException) {
                // Directory might not exist at this commit, skip it
                continue;
            }
        }

        return array_values(array_unique($files));
    }

    public function isValidCommit(string $commitHash): bool
    {
        try {
            $this->runGit(['rev-parse', '--verify', $commitHash . '^{commit}']);

            return true;
        } catch (GitException) {
            return false;
        }
    }

    public function getRepositoryPath(): string
    {
        return $this->repositoryPath;
    }

    /**
     * @param list<string> $arguments
     */
    private function runGit(array $arguments): Process
    {
        $command = array_merge(['git'], $arguments);
        $process = new Process($command, $this->repositoryPath);
        $process->setTimeout(60);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw GitException::commandFailed($e->getProcess()->getErrorOutput(), $e);
        }

        return $process;
    }
}
