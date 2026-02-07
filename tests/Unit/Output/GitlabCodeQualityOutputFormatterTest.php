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

use Phauthentic\BcCheck\Output\GitlabCodeQualityOutputFormatter;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(GitlabCodeQualityOutputFormatter::class)]
final class GitlabCodeQualityOutputFormatterTest extends TestCase
{
    private GitlabCodeQualityOutputFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new GitlabCodeQualityOutputFormatter();
    }

    public function testFormatWithNoBreaks(): void
    {
        $output = new BufferedOutput();

        $this->formatter->format([], $output);

        $result = $output->fetch();
        /** @var list<mixed> $data */
        $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], $data);
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
                className: 'App\\Entity\\User',
                memberName: null,
                type: BcBreakType::ClassMadeFinal,
            ),
        ];

        $this->formatter->format($breaks, $output);

        $result = $output->fetch();
        /** @var list<array{
         *     description: string,
         *     check_name: string,
         *     fingerprint: string,
         *     severity: string,
         *     location: array{path: string, lines: array{begin: int}}
         * }> $data
         */
        $data = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(2, $data);

        // First issue
        $this->assertSame('Method "handle" was removed from class App\\Service', $data[0]['description']);
        $this->assertSame('METHOD_REMOVED', $data[0]['check_name']);
        $this->assertSame('major', $data[0]['severity']);
        $this->assertSame('App/Service.php', $data[0]['location']['path']);
        $this->assertSame(1, $data[0]['location']['lines']['begin']);
        $this->assertNotEmpty($data[0]['fingerprint']);
        $this->assertSame(32, strlen($data[0]['fingerprint'])); // MD5 hash length

        // Second issue
        $this->assertSame('Class was made final', $data[1]['description']);
        $this->assertSame('CLASS_MADE_FINAL', $data[1]['check_name']);
        $this->assertSame('major', $data[1]['severity']);
        $this->assertSame('App/Entity/User.php', $data[1]['location']['path']);
    }

    public function testFingerprintIsConsistent(): void
    {
        $output1 = new BufferedOutput();
        $output2 = new BufferedOutput();

        $break = new BcBreak(
            message: 'Method "handle" was removed',
            className: 'App\\Service',
            memberName: 'handle',
            type: BcBreakType::MethodRemoved,
        );

        $this->formatter->format([$break], $output1);
        $this->formatter->format([$break], $output2);

        /** @var list<array{fingerprint: string}> $data1 */
        $data1 = json_decode($output1->fetch(), true, 512, JSON_THROW_ON_ERROR);
        /** @var list<array{fingerprint: string}> $data2 */
        $data2 = json_decode($output2->fetch(), true, 512, JSON_THROW_ON_ERROR);

        // Same break should produce same fingerprint
        $this->assertSame($data1[0]['fingerprint'], $data2[0]['fingerprint']);
    }

    public function testFingerprintIsDifferentForDifferentBreaks(): void
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

        /** @var list<array{fingerprint: string}> $data */
        $data = json_decode($output->fetch(), true, 512, JSON_THROW_ON_ERROR);

        // Different breaks should have different fingerprints
        $this->assertNotSame($data[0]['fingerprint'], $data[1]['fingerprint']);
    }
}
