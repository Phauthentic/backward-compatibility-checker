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

namespace Phauthentic\BcCheck\Output;

use Phauthentic\BcCheck\ValueObject\BcBreak;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Formats BC breaks as GitLab Code Quality JSON.
 *
 * This format allows GitLab to display code quality issues inline in merge requests.
 *
 * @see https://docs.gitlab.com/ee/ci/testing/code_quality.html#implement-a-custom-tool
 */
final readonly class GitlabCodeQualityOutputFormatter implements OutputFormatterInterface
{
    public function format(array $breaks, OutputInterface $output): void
    {
        $issues = array_map(
            fn (BcBreak $break): array => $this->createIssue($break),
            $breaks,
        );

        $json = json_encode($issues, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $output->writeln($json);
    }

    /**
     * Create a GitLab Code Quality issue from a BC break.
     *
     * @return array{
     *     description: string,
     *     check_name: string,
     *     fingerprint: string,
     *     severity: string,
     *     location: array{path: string, lines: array{begin: int}}
     * }
     */
    private function createIssue(BcBreak $break): array
    {
        return [
            'description' => $break->message,
            'check_name' => $break->type->value,
            'fingerprint' => $this->generateFingerprint($break),
            'severity' => 'major',
            'location' => [
                'path' => $this->classNameToPath($break->className),
                'lines' => [
                    'begin' => 1,
                ],
            ],
        ];
    }

    /**
     * Generate a unique fingerprint for the issue.
     *
     * The fingerprint is used by GitLab to track issues across runs.
     */
    private function generateFingerprint(BcBreak $break): string
    {
        $identifier = $break->getFullIdentifier() . ':' . $break->type->value;

        return md5($identifier);
    }

    /**
     * Convert a class name to a file path.
     *
     * This is a best-effort conversion assuming PSR-4 autoloading.
     */
    private function classNameToPath(string $className): string
    {
        return str_replace('\\', '/', $className) . '.php';
    }
}
