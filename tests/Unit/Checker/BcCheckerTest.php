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

namespace Phauthentic\BcCheck\Tests\Unit\Checker;

use Phauthentic\BcCheck\Checker\BcChecker;
use Phauthentic\BcCheck\Checker\InvalidCommitException;
use Phauthentic\BcCheck\Detector\DetectorRegistry;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzerInterface;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\ClassType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BcChecker::class)]
final class BcCheckerTest extends TestCase
{
    public function testCheckDetectsRemovedClass(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')->willReturn(true);

        $beforeClasses = [
            'App\\Service' => new ClassInfo(name: 'App\\Service'),
        ];
        $afterClasses = [];

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $analyzer->method('analyzeAtCommit')
            ->willReturnOnConsecutiveCalls($beforeClasses, $afterClasses);

        $registry = new DetectorRegistry([]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $breaks = $checker->check('abc123', 'def456');

        $this->assertCount(1, $breaks);
        $this->assertSame(BcBreakType::ClassRemoved, $breaks[0]->type);
        $this->assertStringContainsString('was removed', $breaks[0]->message);
    }

    public function testCheckDetectsRemovedInterface(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')->willReturn(true);

        $beforeClasses = [
            'App\\Contracts\\ServiceInterface' => new ClassInfo(
                name: 'App\\Contracts\\ServiceInterface',
                type: ClassType::Interface,
            ),
        ];
        $afterClasses = [];

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $analyzer->method('analyzeAtCommit')
            ->willReturnOnConsecutiveCalls($beforeClasses, $afterClasses);

        $registry = new DetectorRegistry([]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $breaks = $checker->check('abc123', 'def456');

        $this->assertCount(1, $breaks);
        $this->assertStringContainsString('Interface', $breaks[0]->message);
    }

    public function testCheckUsesDetectorRegistry(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')->willReturn(true);

        $beforeClass = new ClassInfo(name: 'App\\Service', isFinal: false);
        $afterClass = new ClassInfo(name: 'App\\Service', isFinal: true);

        $beforeClasses = ['App\\Service' => $beforeClass];
        $afterClasses = ['App\\Service' => $afterClass];

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $analyzer->method('analyzeAtCommit')
            ->willReturnOnConsecutiveCalls($beforeClasses, $afterClasses);

        $expectedBreak = new BcBreak(
            message: 'Class made final',
            className: 'App\\Service',
            type: BcBreakType::ClassMadeFinal,
        );

        $detector = $this->createStub(\Phauthentic\BcCheck\Detector\BcBreakDetectorInterface::class);
        $detector->method('detect')->willReturn([$expectedBreak]);

        $registry = new DetectorRegistry([$detector]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $breaks = $checker->check('abc123', 'def456');

        $this->assertCount(1, $breaks);
        $this->assertSame($expectedBreak, $breaks[0]);
    }

    public function testCheckThrowsOnInvalidFromCommit(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')
            ->willReturnCallback(fn ($commit) => $commit !== 'invalid');

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $registry = new DetectorRegistry([]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $this->expectException(InvalidCommitException::class);
        $this->expectExceptionMessage('Invalid from commit');

        $checker->check('invalid', 'def456');
    }

    public function testCheckThrowsOnInvalidToCommit(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')
            ->willReturnCallback(fn ($commit) => $commit === 'abc123');

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $registry = new DetectorRegistry([]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $this->expectException(InvalidCommitException::class);
        $this->expectExceptionMessage('Invalid to commit');

        $checker->check('abc123', 'invalid');
    }

    public function testCheckReturnsEmptyWhenNoBreaks(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('isValidCommit')->willReturn(true);

        $classInfo = new ClassInfo(name: 'App\\Service');
        $beforeClasses = ['App\\Service' => $classInfo];
        $afterClasses = ['App\\Service' => $classInfo];

        $analyzer = $this->createStub(CodebaseAnalyzerInterface::class);
        $analyzer->method('analyzeAtCommit')
            ->willReturnOnConsecutiveCalls($beforeClasses, $afterClasses);

        $registry = new DetectorRegistry([]);

        $checker = new BcChecker($analyzer, $registry, $git);

        $breaks = $checker->check('abc123', 'def456');

        $this->assertCount(0, $breaks);
    }
}
