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

use Phauthentic\BcCheck\Output\JsonOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(JsonOutputFormatter::class)]
final class JsonOutputFormatterTest extends TestCase
{
    private JsonOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JsonOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();
        /** @var array{total: int, breaks: list<mixed>} $data */
        $data = json_decode($result, true);

        $this->assertSame(0, $data['total']);
        $this->assertSame([], $data['breaks']);
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
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();
        /** @var array{total: int, breaks: list<array{type: string, message: string, class: string, member: string|null}>} $data */
        $data = json_decode($result, true);

        $this->assertSame(1, $data['total']);
        $this->assertCount(1, $data['breaks']);
        $this->assertSame('METHOD_REMOVED', $data['breaks'][0]['type']);
        $this->assertSame('Method removed', $data['breaks'][0]['message']);
        $this->assertSame('App\\Service', $data['breaks'][0]['class']);
        $this->assertSame('handle', $data['breaks'][0]['member']);
    }
}
