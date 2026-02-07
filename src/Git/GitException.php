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

use Phauthentic\BcCheck\Exception\BcCheckException;
use Throwable;

final class GitException extends BcCheckException
{
    public static function repositoryPathNotFound(string $path): self
    {
        return new self(sprintf('Repository path does not exist: %s', $path));
    }

    public static function notAGitRepository(string $path): self
    {
        return new self(sprintf('Not a git repository: %s', $path));
    }

    public static function commandFailed(string $errorOutput, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Git command failed: %s', $errorOutput),
            $previous,
        );
    }
}
