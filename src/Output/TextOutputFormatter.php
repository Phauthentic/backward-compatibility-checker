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

final readonly class TextOutputFormatter implements OutputFormatterInterface
{
    private const SEPARATOR = '─';

    public function format(array $breaks, OutputInterface $output): void
    {
        if ($breaks === []) {
            $output->writeln('');
            $output->writeln('<info>  ✓ No backward compatibility breaks detected!</info>');
            $output->writeln('');

            return;
        }

        $output->writeln('');
        $output->writeln($this->createSeparator(60));
        $output->writeln(sprintf('<error>  BC CHECK REPORT: %d issue(s) found  </error>', count($breaks)));
        $output->writeln($this->createSeparator(60));
        $output->writeln('');

        // Group breaks by class for better readability
        $groupedByClass = $this->groupBreaksByClass($breaks);

        foreach ($groupedByClass as $className => $classBreaks) {
            $output->writeln(sprintf('<comment>  Class: %s</comment>', $className));
            $output->writeln('');

            foreach ($classBreaks as $break) {
                $this->writeBreak($break, $output);
            }

            $output->writeln('');
        }

        // Summary
        $output->writeln($this->createSeparator(60));
        $output->writeln('<comment>  Summary by type:</comment>');

        $typeCounts = $this->countByType($breaks);
        foreach ($typeCounts as $type => $count) {
            $output->writeln(sprintf('    • %s: <fg=yellow>%d</>', $this->formatTypeName($type), $count));
        }

        $output->writeln('');
        $output->writeln(sprintf('  <fg=red>Total: %d backward compatibility break(s)</>', count($breaks)));
        $output->writeln($this->createSeparator(60));
        $output->writeln('');
    }

    private function writeBreak(BcBreak $break, OutputInterface $output): void
    {
        $typeLabel = $this->formatTypeName($break->type->value);
        $memberInfo = $break->memberName !== null
            ? sprintf(' → <fg=cyan>%s</>', $break->memberName)
            : '';

        $output->writeln(sprintf(
            '    <fg=red>✗</> <fg=yellow>[%s]</>%s',
            $typeLabel,
            $memberInfo,
        ));
        $output->writeln(sprintf('      %s', $break->message));
    }

    /**
     * @param list<BcBreak> $breaks
     * @return array<string, list<BcBreak>>
     */
    private function groupBreaksByClass(array $breaks): array
    {
        $grouped = [];

        foreach ($breaks as $break) {
            $className = $break->className;

            if (!isset($grouped[$className])) {
                $grouped[$className] = [];
            }

            $grouped[$className][] = $break;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @param list<BcBreak> $breaks
     * @return array<string, int>
     */
    private function countByType(array $breaks): array
    {
        $counts = [];

        foreach ($breaks as $break) {
            $type = $break->type->value;

            if (!isset($counts[$type])) {
                $counts[$type] = 0;
            }

            $counts[$type]++;
        }

        arsort($counts);

        return $counts;
    }

    private function formatTypeName(string $type): string
    {
        // Convert SCREAMING_SNAKE_CASE to Title Case
        $words = explode('_', strtolower($type));

        return implode(' ', array_map('ucfirst', $words));
    }

    private function createSeparator(int $length): string
    {
        return str_repeat(self::SEPARATOR, $length);
    }
}
