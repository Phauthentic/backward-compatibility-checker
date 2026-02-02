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

use Phauthentic\BcCheck\Output\GithubActionsFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(GithubActionsFormatter::class)]
final class GithubActionsFormatterTest extends TestCase
{
    private GithubActionsFormatter $formatter;
    private BufferedOutput $output;

    protected function setUp(): void
    {
        $this->formatter = new GithubActionsFormatter();
        $this->output = new BufferedOutput();
    }

    public function testFormatWithNoBreaks(): void
    {
        $this->formatter->format([], $this->output);

        $result = $this->output->fetch();

        $this->assertStringContainsString('::notice::', $result);
        $this->assertStringContainsString('No BC breaks', $result);
    }

    public function testFormatWithBreaks(): void
    {
        $breaks = [
            new BcBreak(
                message: 'Method App\Service::handle() was removed',
                className: 'App\\Service',
                memberName: 'handle',
                type: BcBreakType::MethodRemoved,
            ),
        ];

        $this->formatter->format($breaks, $this->output);

        $result = $this->output->fetch();

        $this->assertStringContainsString('::error title=', $result);
        $this->assertStringContainsString('METHOD_REMOVED', $result);
        $this->assertStringContainsString('was removed', $result);
        $this->assertStringContainsString('Found 1 BC break', $result);
    }

    public function testFormatWithMultipleBreaks(): void
    {
        $breaks = [
            new BcBreak(
                message: 'Method removed',
                className: 'App\\Service',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Class made final',
                className: 'App\\Entity',
                type: BcBreakType::ClassMadeFinal,
            ),
        ];

        $this->formatter->format($breaks, $this->output);

        $result = $this->output->fetch();

        $this->assertStringContainsString('METHOD_REMOVED', $result);
        $this->assertStringContainsString('CLASS_MADE_FINAL', $result);
        $this->assertStringContainsString('Found 2 BC break', $result);
    }

    public function testEscapesSpecialCharacters(): void
    {
        $breaks = [
            new BcBreak(
                message: "Message with\nnewline and : colon",
                className: 'App\\Service',
                type: BcBreakType::Other,
            ),
        ];

        $this->formatter->format($breaks, $this->output);

        $result = $this->output->fetch();

        // Newlines should be escaped as %0A
        $this->assertStringContainsString('%0A', $result);
    }
}
