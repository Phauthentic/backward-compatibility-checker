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

use Phauthentic\BcCheck\ValueObject\BcBreak;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class JsonOutputFormatter implements OutputFormatterInterface
{
    public function format(array $breaks, OutputInterface $output): void
    {
        $data = [
            'total' => count($breaks),
            'breaks' => array_map(
                static fn (BcBreak $break): array => [
                    'type' => $break->type->value,
                    'message' => $break->message,
                    'class' => $break->className,
                    'member' => $break->memberName,
                ],
                $breaks,
            ),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        $output->writeln($json);
    }
}
