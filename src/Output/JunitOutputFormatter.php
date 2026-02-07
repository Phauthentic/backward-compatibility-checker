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

use DOMDocument;
use DOMElement;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Formats BC breaks as JUnit XML.
 *
 * This format is widely supported by CI/CD systems like Jenkins, GitLab CI, CircleCI, etc.
 *
 * @see https://llg.cubic.org/docs/junit/
 */
final readonly class JunitOutputFormatter implements OutputFormatterInterface
{
    private const TESTSUITE_NAME = 'BC Check';

    public function format(array $breaks, OutputInterface $output): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $testsuites = $dom->createElement('testsuites');
        $dom->appendChild($testsuites);

        $testsuite = $dom->createElement('testsuite');
        $testsuite->setAttribute('name', self::TESTSUITE_NAME);
        $testsuite->setAttribute('tests', (string) count($breaks));
        $testsuite->setAttribute('failures', (string) count($breaks));
        $testsuite->setAttribute('errors', '0');
        $testsuites->appendChild($testsuite);

        foreach ($breaks as $break) {
            $testcase = $this->createTestCase($dom, $break);
            $testsuite->appendChild($testcase);
        }

        $xml = $dom->saveXML();
        if ($xml !== false) {
            $output->writeln($xml);
        }
    }

    private function createTestCase(DOMDocument $dom, BcBreak $break): DOMElement
    {
        $testcase = $dom->createElement('testcase');
        $testcase->setAttribute('name', $break->getFullIdentifier());
        $testcase->setAttribute('classname', $break->type->value);

        $failure = $dom->createElement('failure');
        $failure->setAttribute('message', $break->message);
        $failure->setAttribute('type', $break->type->value);
        $testcase->appendChild($failure);

        return $testcase;
    }
}
