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
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class MarkdownOutputFormatter implements OutputFormatterInterface
{
    public function format(array $breaks, OutputInterface $output): void
    {
        if ($breaks === []) {
            $output->writeln('# BC Check Report');
            $output->writeln('');
            $output->writeln('✅ **No backward compatibility breaks detected!**');

            return;
        }

        $output->writeln('# BC Check Report');
        $output->writeln('');
        $output->writeln(sprintf('⚠️ **Found %d backward compatibility break(s)**', count($breaks)));
        $output->writeln('');

        // Group breaks by type category
        $grouped = $this->groupBreaksByCategory($breaks);

        foreach ($grouped as $category => $categoryBreaks) {
            $output->writeln(sprintf('## %s', $category));
            $output->writeln('');
            $output->writeln('| Type | Class | Member | Description |');
            $output->writeln('|------|-------|--------|-------------|');

            foreach ($categoryBreaks as $break) {
                $output->writeln(sprintf(
                    '| `%s` | `%s` | %s | %s |',
                    $break->type->value,
                    $this->escapeMarkdown($break->className),
                    $break->memberName !== null ? '`' . $this->escapeMarkdown($break->memberName) . '`' : '-',
                    $this->escapeMarkdown($break->message),
                ));
            }

            $output->writeln('');
        }

        // Summary section
        $output->writeln('## Summary');
        $output->writeln('');
        $output->writeln('| Category | Count |');
        $output->writeln('|----------|-------|');

        foreach ($grouped as $category => $categoryBreaks) {
            $output->writeln(sprintf('| %s | %d |', $category, count($categoryBreaks)));
        }

        $output->writeln(sprintf('| **Total** | **%d** |', count($breaks)));
        $output->writeln('');
    }

    /**
     * @param list<BcBreak> $breaks
     * @return array<string, list<BcBreak>>
     */
    private function groupBreaksByCategory(array $breaks): array
    {
        $grouped = [];

        foreach ($breaks as $break) {
            $category = $this->getCategoryForType($break->type);

            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            $grouped[$category][] = $break;
        }

        return $grouped;
    }

    private function getCategoryForType(BcBreakType $type): string
    {
        return match ($type) {
            BcBreakType::ClassRemoved,
            BcBreakType::ClassMadeFinal,
            BcBreakType::ClassMadeAbstract,
            BcBreakType::InterfaceRemoved,
            BcBreakType::ParentChanged => 'Class Changes',

            BcBreakType::MethodRemoved,
            BcBreakType::MethodSignatureChanged,
            BcBreakType::MethodReturnTypeChanged,
            BcBreakType::MethodVisibilityReduced,
            BcBreakType::MethodMadeFinal,
            BcBreakType::MethodMadeStatic,
            BcBreakType::MethodMadeNonStatic,
            BcBreakType::MethodMadeAbstract => 'Method Changes',

            BcBreakType::PropertyRemoved,
            BcBreakType::PropertyVisibilityReduced,
            BcBreakType::PropertyTypeChanged,
            BcBreakType::PropertyMadeReadonly,
            BcBreakType::PropertyMadeStatic,
            BcBreakType::PropertyMadeNonStatic => 'Property Changes',

            BcBreakType::ConstantRemoved,
            BcBreakType::ConstantVisibilityReduced => 'Constant Changes',

            BcBreakType::Other => 'Other Changes',
        };
    }

    private function escapeMarkdown(string $text): string
    {
        // Escape pipe characters for table compatibility
        return str_replace('|', '\\|', $text);
    }
}
