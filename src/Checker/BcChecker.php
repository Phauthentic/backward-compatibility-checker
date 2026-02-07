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

use Phauthentic\BcCheck\Detector\DetectorRegistry;
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

        // Analyze both versions
        $beforeClasses = $this->analyzer->analyzeAtCommit($fromCommit);
        $afterClasses = $this->analyzer->analyzeAtCommit($toCommit);

        $breaks = [];

        // Check for removed classes
        foreach ($beforeClasses as $className => $beforeClass) {
            if (!isset($afterClasses[$className])) {
                $breaks[] = new BcBreak(
                    message: sprintf('%s %s was removed', ucfirst($beforeClass->type->value), $className),
                    className: $className,
                    type: BcBreakType::ClassRemoved,
                );

                continue;
            }

            // Compare classes
            $afterClass = $afterClasses[$className];
            $detected = $this->registry->detectAll($beforeClass, $afterClass);

            foreach ($detected as $break) {
                $breaks[] = $break;
            }
        }

        return $breaks;
    }
}
