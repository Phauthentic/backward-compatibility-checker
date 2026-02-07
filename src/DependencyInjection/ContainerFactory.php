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

namespace Phauthentic\BcCheck\DependencyInjection;

use Phauthentic\BcCheck\Checker\BcChecker;
use Phauthentic\BcCheck\Checker\BcCheckerInterface;
use Phauthentic\BcCheck\Config\ConfigurationInterface;
use Phauthentic\BcCheck\Detector\DetectorRegistry;
use Phauthentic\BcCheck\Diff\RenameDetector;
use Phauthentic\BcCheck\Event\FileProcessedEvent;
use Phauthentic\BcCheck\EventHandler\FileProcessedEventHandler;
use Phauthentic\BcCheck\Factory\DetectorRegistryFactory;
use Phauthentic\BcCheck\Git\GitRepository;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzer;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzerInterface;
use Phauthentic\BcCheck\Parser\FileParser;
use Phauthentic\BcCheck\Parser\FileParserInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class ContainerFactory
{
    public function create(
        string $repositoryPath,
        ConfigurationInterface $config,
        ?OutputInterface $output = null,
        bool $showFiles = false,
    ): ContainerBuilder {
        $container = new ContainerBuilder();

        // Register configuration as a service
        $container->set(ConfigurationInterface::class, $config);

        // Register Git repository
        $container->register(GitRepositoryInterface::class, GitRepository::class)
            ->addArgument($repositoryPath);

        // Register file parser
        $container->register(FileParserInterface::class, FileParser::class);

        // Register detector registry
        $container->register(DetectorRegistryFactory::class);
        $container->register(DetectorRegistry::class)
            ->setFactory([new Reference(DetectorRegistryFactory::class), 'createWithConfiguration'])
            ->addArgument(new Reference(ConfigurationInterface::class));

        // Register rename detector
        $container->register(RenameDetector::class);

        // Register message bus (conditionally with handlers)
        $this->registerMessageBus($container, $output, $showFiles);

        // Register codebase analyzer
        $container->register(CodebaseAnalyzerInterface::class, CodebaseAnalyzer::class)
            ->addArgument(new Reference(GitRepositoryInterface::class))
            ->addArgument(new Reference(FileParserInterface::class))
            ->addArgument(new Reference(ConfigurationInterface::class))
            ->addArgument(new Reference(MessageBusInterface::class));

        // Register BC checker
        $container->register(BcCheckerInterface::class, BcChecker::class)
            ->addArgument(new Reference(CodebaseAnalyzerInterface::class))
            ->addArgument(new Reference(DetectorRegistry::class))
            ->addArgument(new Reference(GitRepositoryInterface::class))
            ->addArgument(new Reference(RenameDetector::class))
            ->setPublic(true);

        $container->compile();

        return $container;
    }

    private function registerMessageBus(
        ContainerBuilder $container,
        ?OutputInterface $output,
        bool $showFiles,
    ): void {
        $handlers = [];

        if ($showFiles && $output !== null) {
            $handler = new FileProcessedEventHandler($output);
            $handlers[FileProcessedEvent::class] = [$handler];
        }

        $handlersLocator = new HandlersLocator($handlers);
        $middleware = [new HandleMessageMiddleware($handlersLocator, allowNoHandlers: true)];
        $messageBus = new MessageBus($middleware);

        $container->set(MessageBusInterface::class, $messageBus);
    }
}
