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

namespace Phauthentic\BcCheck\Parser;

use Phauthentic\BcCheck\ValueObject\ClassInfo;

final readonly class AnalysisResult
{
    /**
     * @param array<string, ClassInfo> $classes Map of FQCN to ClassInfo
     * @param array<string, string> $classToFile Map of FQCN to file path
     */
    public function __construct(
        private array $classes,
        private array $classToFile,
    ) {
    }

    /**
     * @return array<string, ClassInfo>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getClass(string $className): ?ClassInfo
    {
        return $this->classes[$className] ?? null;
    }

    public function hasClass(string $className): bool
    {
        return isset($this->classes[$className]);
    }

    public function getFileForClass(string $className): ?string
    {
        return $this->classToFile[$className] ?? null;
    }
}
