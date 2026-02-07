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

namespace Phauthentic\BcCheck\Tests\Unit\Git;

use Phauthentic\BcCheck\Exception\BcCheckException;
use Phauthentic\BcCheck\Git\GitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(GitException::class)]
final class GitExceptionTest extends TestCase
{
    public function testExtendsBaseException(): void
    {
        $exception = GitException::notAGitRepository('/path');

        $this->assertInstanceOf(BcCheckException::class, $exception);
    }

    public function testRepositoryPathNotFound(): void
    {
        $exception = GitException::repositoryPathNotFound('/path/to/repo');

        $this->assertSame('Repository path does not exist: /path/to/repo', $exception->getMessage());
    }

    public function testNotAGitRepository(): void
    {
        $exception = GitException::notAGitRepository('/path/to/repo');

        $this->assertSame('Not a git repository: /path/to/repo', $exception->getMessage());
    }

    public function testCommandFailed(): void
    {
        $exception = GitException::commandFailed('fatal: not a git repository');

        $this->assertSame('Git command failed: fatal: not a git repository', $exception->getMessage());
    }

    public function testCommandFailedWithPrevious(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = GitException::commandFailed('fatal: not a git repository', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
