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

namespace Phauthentic\BcCheck\Tests\Unit\Checker;

use Phauthentic\BcCheck\Checker\InvalidCommitException;
use Phauthentic\BcCheck\Exception\BcCheckException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidCommitException::class)]
final class InvalidCommitExceptionTest extends TestCase
{
    public function testExtendsBaseException(): void
    {
        $exception = InvalidCommitException::invalidCommit('abc123');

        $this->assertInstanceOf(BcCheckException::class, $exception);
    }

    public function testInvalidCommit(): void
    {
        $exception = InvalidCommitException::invalidCommit('abc123');

        $this->assertSame('Invalid commit: abc123', $exception->getMessage());
    }

    public function testInvalidFromCommit(): void
    {
        $exception = InvalidCommitException::invalidFromCommit('abc123');

        $this->assertSame('Invalid from commit: abc123', $exception->getMessage());
    }

    public function testInvalidToCommit(): void
    {
        $exception = InvalidCommitException::invalidToCommit('def456');

        $this->assertSame('Invalid to commit: def456', $exception->getMessage());
    }
}
