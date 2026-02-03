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

namespace Phauthentic\BcCheck\Parser;

use Phauthentic\BcCheck\Config\ConfigurationInterface;
use Phauthentic\BcCheck\Event\FileProcessedEvent;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final readonly class CodebaseAnalyzer implements CodebaseAnalyzerInterface
{
    public function __construct(
        private GitRepositoryInterface $git,
        private FileParserInterface $parser,
        private ConfigurationInterface $config,
        private ?MessageBusInterface $messageBus = null,
    ) {
    }

    /**
     * Analyze the codebase at a specific commit.
     *
     * @param string $label Optional label for event reporting (e.g., "source", "target")
     * @return array<string, ClassInfo> Map of FQCN to ClassInfo
     */
    public function analyzeAtCommit(string $commitHash, string $label = ''): array
    {
        return $this->analyzeAtCommitWithFileMap($commitHash, $label)->getClasses();
    }

    /**
     * Analyze the codebase at a specific commit with file mapping.
     *
     * @param string $label Optional label for event reporting (e.g., "source", "target")
     */
    public function analyzeAtCommitWithFileMap(string $commitHash, string $label = ''): AnalysisResult
    {
        $files = $this->git->getPhpFilesAtCommit(
            $commitHash,
            $this->config->getSourceDirectories(),
        );

        $classes = [];
        $classToFile = [];

        foreach ($files as $file) {
            $this->messageBus?->dispatch(new FileProcessedEvent($file, $commitHash, $label));

            try {
                $content = $this->git->getFileContentAtCommit($commitHash, $file);
                $parsedClasses = $this->parser->parse($content);

                foreach ($parsedClasses as $classInfo) {
                    if ($this->config->shouldInclude($classInfo->name)) {
                        $classes[$classInfo->name] = $classInfo;
                        $classToFile[$classInfo->name] = $file;
                    }
                }
            } catch (Throwable) {
                // Skip files that cannot be parsed
                continue;
            }
        }

        return new AnalysisResult($classes, $classToFile);
    }
}
