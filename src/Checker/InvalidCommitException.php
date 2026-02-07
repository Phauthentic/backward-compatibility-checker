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

namespace Phauthentic\BcCheck\Checker;

use Phauthentic\BcCheck\Exception\BcCheckException;

final class InvalidCommitException extends BcCheckException
{
    public static function invalidCommit(string $commit): self
    {
        return new self(sprintf('Invalid commit: %s', $commit));
    }

    public static function invalidFromCommit(string $commit): self
    {
        return new self(sprintf('Invalid from commit: %s', $commit));
    }

    public static function invalidToCommit(string $commit): self
    {
        return new self(sprintf('Invalid to commit: %s', $commit));
    }
}
