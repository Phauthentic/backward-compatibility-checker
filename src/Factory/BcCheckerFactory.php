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

namespace Phauthentic\BcCheck\Factory;

use Phauthentic\BcCheck\Checker\BcChecker;
use Phauthentic\BcCheck\Checker\BcCheckerInterface;
use Phauthentic\BcCheck\Config\ConfigurationInterface;
use Phauthentic\BcCheck\Git\GitRepository;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzer;
use Phauthentic\BcCheck\Parser\FileParser;

final readonly class BcCheckerFactory
{
    public function __construct(
        private DetectorRegistryFactory $registryFactory = new DetectorRegistryFactory(),
    ) {
    }

    public function create(
        string $repositoryPath,
        ConfigurationInterface $config,
    ): BcCheckerInterface {
        $git = new GitRepository($repositoryPath);

        return $this->createWithGit($git, $config);
    }

    public function createWithGit(
        GitRepositoryInterface $git,
        ConfigurationInterface $config,
    ): BcCheckerInterface {
        $parser = new FileParser();
        $analyzer = new CodebaseAnalyzer($git, $parser, $config);
        $registry = $this->registryFactory->createWithConfiguration($config);

        return new BcChecker($analyzer, $registry, $git);
    }
}
