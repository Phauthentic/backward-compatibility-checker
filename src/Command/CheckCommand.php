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

namespace Phauthentic\BcCheck\Command;

use InvalidArgumentException;
use Phauthentic\BcCheck\Checker\BcCheckerInterface;
use Phauthentic\BcCheck\Config\Configuration;
use Phauthentic\BcCheck\Config\ConfigurationException;
use Phauthentic\BcCheck\Config\ConfigurationLoader;
use Phauthentic\BcCheck\DependencyInjection\ContainerFactory;
use Phauthentic\BcCheck\Git\GitException;
use Phauthentic\BcCheck\Output\CheckstyleOutputFormatter;
use Phauthentic\BcCheck\Output\GithubActionsFormatter;
use Phauthentic\BcCheck\Output\GitlabCodeQualityOutputFormatter;
use Phauthentic\BcCheck\Output\JsonOutputFormatter;
use Phauthentic\BcCheck\Output\JunitOutputFormatter;
use Phauthentic\BcCheck\Output\MarkdownOutputFormatter;
use Phauthentic\BcCheck\Output\OutputFormatterInterface;
use Phauthentic\BcCheck\Output\SarifOutputFormatter;
use Phauthentic\BcCheck\Output\TextOutputFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'check',
    description: 'Check for backward compatibility breaks between two git commits',
)]
final class CheckCommand extends Command
{
    private const FORMAT_TEXT = 'text';
    private const FORMAT_JSON = 'json';
    private const FORMAT_MARKDOWN = 'markdown';
    private const FORMAT_GITHUB = 'github-actions';
    private const FORMAT_SARIF = 'sarif';
    private const FORMAT_CHECKSTYLE = 'checkstyle';
    private const FORMAT_JUNIT = 'junit';
    private const FORMAT_GITLAB = 'gitlab';

    protected function configure(): void
    {
        $this
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Path to the git repository',
            )
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'The base commit hash (older version)',
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'The target commit hash (newer version)',
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to configuration file',
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Output format (%s, %s, %s, %s, %s, %s, %s, %s)',
                    self::FORMAT_TEXT,
                    self::FORMAT_JSON,
                    self::FORMAT_MARKDOWN,
                    self::FORMAT_GITHUB,
                    self::FORMAT_SARIF,
                    self::FORMAT_CHECKSTYLE,
                    self::FORMAT_JUNIT,
                    self::FORMAT_GITLAB,
                ),
                self::FORMAT_TEXT,
            )
            ->addOption(
                'show-files',
                null,
                InputOption::VALUE_NONE,
                'Show files being processed',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $repositoryPath */
        $repositoryPath = $input->getArgument('repository');
        /** @var string $fromCommit */
        $fromCommit = $input->getArgument('from');
        /** @var string $toCommit */
        $toCommit = $input->getArgument('to');
        /** @var string|null $configPath */
        $configPath = $input->getOption('config');
        /** @var string $format */
        $format = $input->getOption('format');
        /** @var bool $showFiles */
        $showFiles = $input->getOption('show-files');

        // Resolve repository path
        if (!str_starts_with($repositoryPath, '/')) {
            $repositoryPath = getcwd() . '/' . $repositoryPath;
        }

        $repositoryPath = realpath($repositoryPath);
        if ($repositoryPath === false) {
            $output->writeln('<error>Repository path does not exist</error>');

            return Command::FAILURE;
        }

        // Load configuration
        try {
            $config = $this->loadConfiguration($configPath);
        } catch (ConfigurationException $e) {
            $output->writeln(sprintf('<error>Configuration error: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        // Get formatter
        $formatter = $this->getFormatter($format);

        // Build container with services
        try {
            $containerFactory = new ContainerFactory();
            $container = $containerFactory->create(
                $repositoryPath,
                $config,
                $output,
                $showFiles,
            );

            /** @var BcCheckerInterface $checker */
            $checker = $container->get(BcCheckerInterface::class);
        } catch (GitException $e) {
            $output->writeln(sprintf('<error>Git error: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        // Run check
        $startTime = microtime(true);
        try {
            $breaks = $checker->check($fromCommit, $toCommit);
        } catch (InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        } catch (GitException $e) {
            $output->writeln(sprintf('<error>Git error: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
        $duration = microtime(true) - $startTime;

        // Output results
        $formatter->format($breaks, $output);

        // Output timing
        $output->writeln('');
        $output->writeln(sprintf('Time: %.2fs', $duration));

        return $breaks === [] ? Command::SUCCESS : Command::FAILURE;
    }

    private function loadConfiguration(?string $configPath): Configuration
    {
        $loader = new ConfigurationLoader();

        if ($configPath !== null) {
            return $loader->load($configPath);
        }

        // Try to find config file in current directory
        $defaultPaths = ['bc-check.yaml', 'bc-check.yml', 'bc-check.yaml.dist'];

        foreach ($defaultPaths as $path) {
            if (file_exists($path)) {
                return $loader->load($path);
            }
        }

        return $loader->createDefault();
    }

    private function getFormatter(string $format): OutputFormatterInterface
    {
        return match ($format) {
            self::FORMAT_JSON => new JsonOutputFormatter(),
            self::FORMAT_MARKDOWN => new MarkdownOutputFormatter(),
            self::FORMAT_GITHUB => new GithubActionsFormatter(),
            self::FORMAT_SARIF => new SarifOutputFormatter(),
            self::FORMAT_CHECKSTYLE => new CheckstyleOutputFormatter(),
            self::FORMAT_JUNIT => new JunitOutputFormatter(),
            self::FORMAT_GITLAB => new GitlabCodeQualityOutputFormatter(),
            default => new TextOutputFormatter(),
        };
    }
}
