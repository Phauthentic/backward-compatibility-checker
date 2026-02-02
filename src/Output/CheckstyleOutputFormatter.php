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

use DOMDocument;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Formats BC breaks as Checkstyle XML.
 *
 * This format is widely supported by CI/CD systems and IDEs.
 *
 * @see https://checkstyle.sourceforge.io/
 */
final readonly class CheckstyleOutputFormatter implements OutputFormatterInterface
{
    private const VERSION = '1.0.0';

    public function format(array $breaks, OutputInterface $output): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $checkstyle = $dom->createElement('checkstyle');
        $checkstyle->setAttribute('version', self::VERSION);
        $dom->appendChild($checkstyle);

        // Group breaks by class name
        $groupedBreaks = $this->groupByClassName($breaks);

        foreach ($groupedBreaks as $className => $classBreaks) {
            $file = $dom->createElement('file');
            $file->setAttribute('name', $className);

            foreach ($classBreaks as $break) {
                $error = $dom->createElement('error');
                $error->setAttribute('severity', 'error');
                $error->setAttribute('message', $break->message);
                $error->setAttribute('source', 'bc-check.' . $break->type->value);

                $file->appendChild($error);
            }

            $checkstyle->appendChild($file);
        }

        $xml = $dom->saveXML();
        if ($xml !== false) {
            $output->writeln($xml);
        }
    }

    /**
     * Group BC breaks by class name.
     *
     * @param list<BcBreak> $breaks
     * @return array<string, list<BcBreak>>
     */
    private function groupByClassName(array $breaks): array
    {
        $grouped = [];

        foreach ($breaks as $break) {
            $grouped[$break->className][] = $break;
        }

        return $grouped;
    }
}
