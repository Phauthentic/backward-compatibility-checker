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

use Phauthentic\BcCheck\Output\SarifOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(SarifOutputFormatter::class)]
final class SarifOutputFormatterTest extends TestCase
{
    private SarifOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new SarifOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();
        /** @var array{
         *     '$schema': string,
         *     version: string,
         *     runs: list<array{
         *         tool: array{driver: array{name: string, version: string, rules: list<mixed>}},
         *         results: list<mixed>
         *     }>
         * } $data
         */
        $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('https://json.schemastore.org/sarif-2.1.0.json', $data['$schema']);
        $this->assertSame('2.1.0', $data['version']);
        $this->assertCount(1, $data['runs']);
        $this->assertSame('php-bc-check', $data['runs'][0]['tool']['driver']['name']);
        $this->assertSame([], $data['runs'][0]['tool']['driver']['rules']);
        $this->assertSame([], $data['runs'][0]['results']);
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
                message: 'Property "name" was removed from class App\\Service',
                className: 'App\\Service',
                memberName: 'name',
                type: BcBreakType::PropertyRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();
        /** @var array{
         *     '$schema': string,
         *     version: string,
         *     runs: list<array{
         *         tool: array{driver: array{name: string, version: string, rules: list<array{id: string, shortDescription: array{text: string}}>}},
         *         results: list<array{ruleId: string, level: string, message: array{text: string}}>
         *     }>
         * } $data
         */
        $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        // Verify structure
        $this->assertSame('2.1.0', $data['version']);
        $this->assertCount(1, $data['runs']);

        // Verify rules (should have 2 unique rules)
        $rules = $data['runs'][0]['tool']['driver']['rules'];
        $this->assertCount(2, $rules);

        $ruleIds = array_column($rules, 'id');
        $this->assertContains('METHOD_REMOVED', $ruleIds);
        $this->assertContains('PROPERTY_REMOVED', $ruleIds);

        // Verify results
        $results = $data['runs'][0]['results'];
        $this->assertCount(2, $results);

        $this->assertSame('METHOD_REMOVED', $results[0]['ruleId']);
        $this->assertSame('error', $results[0]['level']);
        $this->assertStringContainsString('handle', $results[0]['message']['text']);

        $this->assertSame('PROPERTY_REMOVED', $results[1]['ruleId']);
        $this->assertSame('error', $results[1]['level']);
        $this->assertStringContainsString('name', $results[1]['message']['text']);
    }

    public function testFormatWithDuplicateRuleTypes(): void
    {
        $output = new BufferedOutput();
        $breaks = [
            new BcBreak(
                message: 'Method "foo" was removed',
                className: 'App\\Foo',
                memberName: 'foo',
                type: BcBreakType::MethodRemoved,
            ),
            new BcBreak(
                message: 'Method "bar" was removed',
                className: 'App\\Bar',
                memberName: 'bar',
                type: BcBreakType::MethodRemoved,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();
        /** @var array{runs: list<array{tool: array{driver: array{rules: list<mixed>}}, results: list<mixed>}>} $data */
        $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        // Should only have 1 rule even with 2 breaks of the same type
        $this->assertCount(1, $data['runs'][0]['tool']['driver']['rules']);
        // But should have 2 results
        $this->assertCount(2, $data['runs'][0]['results']);
    }
}
