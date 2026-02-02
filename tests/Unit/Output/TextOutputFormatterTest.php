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

namespace Phauthentic\BcCheck\Tests\Unit\Output;

use Phauthentic\BcCheck\Output\TextOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(TextOutputFormatter::class)]
final class TextOutputFormatterTest extends TestCase
{
    private TextOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new TextOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();
        $this->assertStringContainsString('No backward compatibility breaks detected', $result);
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
        $this->assertStringContainsString('2 issue(s) found', $result);
        $this->assertStringContainsString('2 backward compatibility break(s)', $result);
        $this->assertStringContainsString('Method Removed', $result);
        $this->assertStringContainsString('Property Removed', $result);
        $this->assertStringContainsString('Class: App\\Service', $result);
        $this->assertStringContainsString('handle', $result);
        $this->assertStringContainsString('name', $result);
    }
}
