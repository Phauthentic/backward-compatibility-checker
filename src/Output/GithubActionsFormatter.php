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

namespace Phauthentic\BcCheck\Output;

use Symfony\Component\Console\Output\OutputInterface;

final readonly class GithubActionsFormatter implements OutputFormatterInterface
{
    public function format(array $breaks, OutputInterface $output): void
    {
        if ($breaks === []) {
            $output->writeln('::notice::No BC breaks detected');

            return;
        }

        foreach ($breaks as $break) {
            // GitHub Actions annotation format
            $output->writeln(sprintf(
                '::error title=%s::%s',
                $this->escapeProperty($break->type->value),
                $this->escapeData($break->message),
            ));
        }

        $output->writeln(sprintf(
            '::error::Found %d BC break(s)',
            count($breaks),
        ));
    }

    private function escapeProperty(string $value): string
    {
        return str_replace(
            ['%', "\r", "\n", ':'],
            ['%25', '%0D', '%0A', '%3A'],
            $value,
        );
    }

    private function escapeData(string $value): string
    {
        return str_replace(
            ['%', "\r", "\n"],
            ['%25', '%0D', '%0A'],
            $value,
        );
    }
}
