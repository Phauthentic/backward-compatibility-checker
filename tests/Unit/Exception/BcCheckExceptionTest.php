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

namespace Phauthentic\BcCheck\Tests\Unit\Exception;

use Phauthentic\BcCheck\Config\ConfigurationException;
use Phauthentic\BcCheck\Exception\BcCheckException;
use Phauthentic\BcCheck\Git\GitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BcCheckException::class)]
final class BcCheckExceptionTest extends TestCase
{
    public function testAllExceptionsExtendBaseException(): void
    {
        $gitException = GitException::notAGitRepository('/path');
        $configException = ConfigurationException::fileNotFound('/path');

        $this->assertInstanceOf(BcCheckException::class, $gitException);
        $this->assertInstanceOf(BcCheckException::class, $configException);
    }
}
