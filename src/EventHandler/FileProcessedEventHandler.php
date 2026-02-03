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

namespace Phauthentic\BcCheck\EventHandler;

use Phauthentic\BcCheck\Event\FileProcessedEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FileProcessedEventHandler
{
    public function __construct(
        private OutputInterface $output,
    ) {
    }

    public function __invoke(FileProcessedEvent $event): void
    {
        $label = $event->label !== '' ? sprintf(' (%s)', $event->label) : '';
        $this->output->writeln(sprintf('Processing%s: %s', $label, $event->file));
    }
}
