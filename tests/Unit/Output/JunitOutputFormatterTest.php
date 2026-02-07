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

namespace Phauthentic\BcCheck\Tests\Unit\Output;

use DOMDocument;
use Phauthentic\BcCheck\Output\JunitOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(JunitOutputFormatter::class)]
final class JunitOutputFormatterTest extends TestCase
{
    private JunitOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JunitOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();

        // Verify it's valid XML
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($result));

        // Verify structure
        $testsuites = $dom->documentElement;
        $this->assertNotNull($testsuites);
        $this->assertSame('testsuites', $testsuites->nodeName);

        $testsuite = $testsuites->getElementsByTagName('testsuite')->item(0);
        $this->assertNotNull($testsuite);
        $this->assertSame('BC Check', $testsuite->getAttribute('name'));
        $this->assertSame('0', $testsuite->getAttribute('tests'));
        $this->assertSame('0', $testsuite->getAttribute('failures'));
        $this->assertSame('0', $testsuite->getAttribute('errors'));
    }

    public function testFormatWithBreaks(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Method "handle" was removed from class App\\Service',
                className: 'App\\Service',
                memberName: 'handle',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Class was made final',
                className: 'App\\Entity',
                memberName: null,
                type: BcBreakType::ClassMadeFinal,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        // Verify it's valid XML
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($result));

        // Verify testsuite
        $testsuite = $dom->getElementsByTagName('testsuite')->item(0);
        $this->assertNotNull($testsuite);
        $this->assertSame('2', $testsuite->getAttribute('tests'));
        $this->assertSame('2', $testsuite->getAttribute('failures'));

        // Verify testcases
        $testcases = $dom->getElementsByTagName('testcase');
        $this->assertSame(2, $testcases->length);

        // First testcase (with member name)
        $testcase1 = $testcases->item(0);
        $this->assertNotNull($testcase1);
        $this->assertSame('App\\Service::handle', $testcase1->getAttribute('name'));
        $this->assertSame('METHOD_REMOVED', $testcase1->getAttribute('classname'));

        $failure1 = $testcase1->getElementsByTagName('failure')->item(0);
        $this->assertNotNull($failure1);
        $this->assertSame('Method "handle" was removed from class App\\Service', $failure1->getAttribute('message'));
        $this->assertSame('METHOD_REMOVED', $failure1->getAttribute('type'));

        // Second testcase (without member name)
        $testcase2 = $testcases->item(1);
        $this->assertNotNull($testcase2);
        $this->assertSame('App\\Entity', $testcase2->getAttribute('name'));
        $this->assertSame('CLASS_MADE_FINAL', $testcase2->getAttribute('classname'));

        $failure2 = $testcase2->getElementsByTagName('failure')->item(0);
        $this->assertNotNull($failure2);
        $this->assertSame('Class was made final', $failure2->getAttribute('message'));
    }
}
