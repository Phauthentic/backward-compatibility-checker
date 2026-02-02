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

namespace Phauthentic\BcCheck\Tests\Unit\Parser;

use Phauthentic\BcCheck\Config\Configuration;
use Phauthentic\BcCheck\Git\GitRepositoryInterface;
use Phauthentic\BcCheck\Parser\CodebaseAnalyzer;
use Phauthentic\BcCheck\Parser\FileParserInterface;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(CodebaseAnalyzer::class)]
final class CodebaseAnalyzerTest extends TestCase
{
    public function testAnalyzeAtCommitReturnsClasses(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('getPhpFilesAtCommit')->willReturn(['src/Service.php']);
        $git->method('getFileContentAtCommit')->willReturn('<?php class Service {}');

        $classInfo = new ClassInfo(name: 'App\\Service');
        $parser = $this->createStub(FileParserInterface::class);
        $parser->method('parse')->willReturn([$classInfo]);

        $config = new Configuration();

        $analyzer = new CodebaseAnalyzer($git, $parser, $config);

        $result = $analyzer->analyzeAtCommit('abc123');

        $this->assertArrayHasKey('App\\Service', $result);
        $this->assertSame($classInfo, $result['App\\Service']);
    }

    public function testAnalyzeAtCommitFiltersExcludedClasses(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('getPhpFilesAtCommit')->willReturn(['src/Service.php']);
        $git->method('getFileContentAtCommit')->willReturn('<?php class Internal {}');

        $classInfo = new ClassInfo(name: 'App\\Internal\\Service');
        $parser = $this->createStub(FileParserInterface::class);
        $parser->method('parse')->willReturn([$classInfo]);

        $config = new Configuration(excludePatterns: ['.*Internal.*']);

        $analyzer = new CodebaseAnalyzer($git, $parser, $config);

        $result = $analyzer->analyzeAtCommit('abc123');

        $this->assertEmpty($result);
    }

    public function testAnalyzeAtCommitSkipsUnparseableFiles(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('getPhpFilesAtCommit')->willReturn(['src/Bad.php', 'src/Good.php']);
        $git->method('getFileContentAtCommit')
            ->willReturnCallback(function (string $commit, string $file): string {
                if ($file === 'src/Bad.php') {
                    throw new RuntimeException('Parse error');
                }

                return '<?php class Good {}';
            });

        $classInfo = new ClassInfo(name: 'App\\Good');
        $parser = $this->createStub(FileParserInterface::class);
        $parser->method('parse')->willReturn([$classInfo]);

        $config = new Configuration();

        $analyzer = new CodebaseAnalyzer($git, $parser, $config);

        $result = $analyzer->analyzeAtCommit('abc123');

        $this->assertArrayHasKey('App\\Good', $result);
        $this->assertCount(1, $result);
    }

    public function testAnalyzeAtCommitReturnsEmptyForNoFiles(): void
    {
        $git = $this->createStub(GitRepositoryInterface::class);
        $git->method('getPhpFilesAtCommit')->willReturn([]);

        $parser = $this->createStub(FileParserInterface::class);
        $config = new Configuration();

        $analyzer = new CodebaseAnalyzer($git, $parser, $config);

        $result = $analyzer->analyzeAtCommit('abc123');

        $this->assertEmpty($result);
    }

    public function testAnalyzeAtCommitUsesSourceDirectories(): void
    {
        $git = $this->createMock(GitRepositoryInterface::class);
        $git->expects($this->once())
            ->method('getPhpFilesAtCommit')
            ->with('abc123', ['lib/'])
            ->willReturn([]);

        $parser = $this->createStub(FileParserInterface::class);
        $config = new Configuration(sourceDirectories: ['lib/']);

        $analyzer = new CodebaseAnalyzer($git, $parser, $config);

        $analyzer->analyzeAtCommit('abc123');
    }
}
