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

namespace Phauthentic\BcCheck\Checker;

use Phauthentic\BcCheck\Detector\DetectorRegistry;
use Phauthentic\BcCheck\Diff\RenameDetector;
use Phauthentic\BcCheck\Diff\RenameMap;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzerInterface;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;

final readonly class BcChecker implements BcCheckerInterface
{
    public function __construct(
        private CodebaseAnalyzerInterface $analyzer,
        private DetectorRegistry $registry,
        private GitRepositoryInterface $git,
        private RenameDetector $renameDetector = new RenameDetector(),
    ) {
    }

    public function check(string $fromCommit, string $toCommit): array
    {
        // Validate commits
        if (!$this->git->isValidCommit($fromCommit)) {
            throw InvalidCommitException::invalidFromCommit($fromCommit);
        }

        if (!$this->git->isValidCommit($toCommit)) {
            throw InvalidCommitException::invalidToCommit($toCommit);
        }

        // Detect renames from diff
        $diff = $this->git->getDiff($fromCommit, $toCommit);
        $renamesByFile = $this->renameDetector->detect($diff);

        // Analyze both versions with file mapping
        $beforeResult = $this->analyzer->analyzeAtCommitWithFileMap($fromCommit);
        $afterResult = $this->analyzer->analyzeAtCommitWithFileMap($toCommit);

        $breaks = [];

        // Check for removed classes
        foreach ($beforeResult->getClasses() as $className => $beforeClass) {
            if (!$afterResult->hasClass($className)) {
                $breaks[] = new BcBreak(
                    message: sprintf('%s %s was removed', ucfirst($beforeClass->type->value), $className),
                    className: $className,
                    type: BcBreakType::ClassRemoved,
                );

                continue;
            }

            // Get rename map for this class's file
            $filePath = $beforeResult->getFileForClass($className);
            $renameMap = $filePath !== null ? ($renamesByFile[$filePath] ?? null) : null;

            // Compare classes - we know afterClass exists because hasClass was true
            /** @var \Phauthentic\BcCheck\ValueObject\ClassInfo $afterClass */
            $afterClass = $afterResult->getClass($className);
            $detected = $this->registry->detectAll($beforeClass, $afterClass, $renameMap);

            foreach ($detected as $break) {
                $breaks[] = $break;
            }
        }

        return $breaks;
    }
}
