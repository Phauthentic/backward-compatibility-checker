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

namespace Phauthentic\BcCheck\Parser;

use Phauthentic\BcCheck\Config\ConfigurationInterface;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Throwable;

final readonly class CodebaseAnalyzer implements CodebaseAnalyzerInterface
{
    public function __construct(
        private GitRepositoryInterface $git,
        private FileParserInterface $parser,
        private ConfigurationInterface $config,
    ) {
    }

    /**
     * Analyze the codebase at a specific commit.
     *
     * @return array<string, ClassInfo> Map of FQCN to ClassInfo
     */
    public function analyzeAtCommit(string $commitHash): array
    {
        $files = $this->git->getPhpFilesAtCommit(
            $commitHash,
            $this->config->getSourceDirectories(),
        );

        $classes = [];

        foreach ($files as $file) {
            try {
                $content = $this->git->getFileContentAtCommit($commitHash, $file);
                $parsedClasses = $this->parser->parse($content);

                foreach ($parsedClasses as $classInfo) {
                    if ($this->config->shouldInclude($classInfo->name)) {
                        $classes[$classInfo->name] = $classInfo;
                    }
                }
            } catch (Throwable) {
                // Skip files that cannot be parsed
                continue;
            }
        }

        return $classes;
    }
}
