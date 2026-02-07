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

use Phauthentic\BcCheck\Output\MarkdownOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(MarkdownOutputFormatter::class)]
final class MarkdownOutputFormatterTest extends TestCase
{
    private MarkdownOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new MarkdownOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();
        $this->assertStringContainsString('# BC Check Report', $result);
        $this->assertStringContainsString('No backward compatibility breaks detected', $result);
        $this->assertStringContainsString('✅', $result);
    }

    public function testFormatWithBreaks(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Method removed',
                className: 'App\\Service',
                memberName: 'handle',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Property removed',
                className: 'App\\Service',
                memberName: 'name',
                type: BcBreakType::PropertyRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        // Check header
        $this->assertStringContainsString('# BC Check Report', $result);
        $this->assertStringContainsString('2 backward compatibility break(s)', $result);

        // Check table structure
        $this->assertStringContainsString('| Type | Class | Member | Description |', $result);
        $this->assertStringContainsString('|------|-------|--------|-------------|', $result);

        // Check content
        $this->assertStringContainsString('`METHOD_REMOVED`', $result);
        $this->assertStringContainsString('`PROPERTY_REMOVED`', $result);
        $this->assertStringContainsString('`App\\Service`', $result);
        $this->assertStringContainsString('`handle`', $result);
        $this->assertStringContainsString('`name`', $result);

        // Check category headers
        $this->assertStringContainsString('## Method Changes', $result);
        $this->assertStringContainsString('## Property Changes', $result);

        // Check summary section
        $this->assertStringContainsString('## Summary', $result);
        $this->assertStringContainsString('| **Total** | **2** |', $result);
    }

    public function testFormatGroupsBreaksByCategory(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Class made final',
                className: 'App\\Entity',
                memberName: null,
                type: BcBreakType::ClassMadeFinal,
            ),
            new BcBreak(
                message: 'Constant removed',
                className: 'App\\Config',
                memberName: 'VERSION',
                type: BcBreakType::ConstantRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        $this->assertStringContainsString('## Class Changes', $result);
        $this->assertStringContainsString('## Constant Changes', $result);
    }

    public function testFormatEscapesPipeCharacters(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Type changed from int|string to int',
                className: 'App\\Service',
                memberName: 'value',
                type: BcBreakType::PropertyTypeChanged,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();

        // Pipe characters should be escaped in markdown tables
        $this->assertStringContainsString('int\\|string', $result);
    }
}
