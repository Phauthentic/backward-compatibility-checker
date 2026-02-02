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

namespace Phauthentic\BcCheck\Tests\Unit\Factory;

use Phauthentic\BcCheck\Checker\BcCheckerInterface;
use Phauthentic\BcCheck\Config\Configuration;
use Phauthentic\BcCheck\Factory\BcCheckerFactory;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BcCheckerFactory::class)]
final class BcCheckerFactoryTest extends TestCase
{
    public function testCreateWithGit(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $config = new Configuration();

        $factory = new BcCheckerFactory();

        $checker = $factory->createWithGit($git, $config);

        $this->assertInstanceOf(BcCheckerInterface::class, $checker);
    }
}
