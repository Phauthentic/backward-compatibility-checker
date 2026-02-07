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
use Phauthentic\BcCheck\Output\CheckstyleOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(CheckstyleOutputFormatter::class)]
final class CheckstyleOutputFormatterTest extends TestCase
{
    private CheckstyleOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new CheckstyleOutputFormatter();
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
        $checkstyle = $dom->documentElement;
        $this->assertNotNull($checkstyle);
        $this->assertSame('checkstyle', $checkstyle->nodeName);
        $this->assertSame('1.0.0', $checkstyle->getAttribute('version'));
        $this->assertSame(0, $checkstyle->childNodes->length);
    }

    public function testFormatWithBreaks(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Method "handle" was removed',
                className: 'App\\Service',
                memberName: 'handle',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Property "name" was removed',
                className: 'App\\Service',
                memberName: 'name',
                type: BcBreakType::PropertyRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        // Verify it's valid XML
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($result));

        // Verify structure
        $checkstyle = $dom->documentElement;
        $this->assertNotNull($checkstyle);
        $this->assertSame('checkstyle', $checkstyle->nodeName);

        // Should have 1 file element (both breaks are in the same class)
        $files = $checkstyle->getElementsByTagName('file');
        $this->assertSame(1, $files->length);

        $file = $files->item(0);
        $this->assertNotNull($file);
        $this->assertSame('App\\Service', $file->getAttribute('name'));

        // Should have 2 error elements
        $errors = $file->getElementsByTagName('error');
        $this->assertSame(2, $errors->length);

        // Verify first error
        $error1 = $errors->item(0);
        $this->assertNotNull($error1);
        $this->assertSame('error', $error1->getAttribute('severity'));
        $this->assertSame('Method "handle" was removed', $error1->getAttribute('message'));
        $this->assertSame('bc-check.METHOD_REMOVED', $error1->getAttribute('source'));

        // Verify second error
        $error2 = $errors->item(1);
        $this->assertNotNull($error2);
        $this->assertSame('error', $error2->getAttribute('severity'));
        $this->assertSame('Property "name" was removed', $error2->getAttribute('message'));
        $this->assertSame('bc-check.PROPERTY_REMOVED', $error2->getAttribute('source'));
    }

    public function testFormatGroupsByClassName(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Method removed from Foo',
                className: 'App\\Foo',
                memberName: 'method',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Method removed from Bar',
                className: 'App\\Bar',
                memberName: 'method',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Another method removed from Foo',
                className: 'App\\Foo',
                memberName: 'anotherMethod',
                type: BcBreakType::MethodRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($result));

        $checkstyle = $dom->documentElement;
        $this->assertNotNull($checkstyle);

        // Should have 2 file elements (App\Foo and App\Bar)
        $files = $checkstyle->getElementsByTagName('file');
        $this->assertSame(2, $files->length);

        // Get file names
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = $file->getAttribute('name');
        }
        $this->assertContains('App\\Foo', $fileNames);
        $this->assertContains('App\\Bar', $fileNames);
    }
}
