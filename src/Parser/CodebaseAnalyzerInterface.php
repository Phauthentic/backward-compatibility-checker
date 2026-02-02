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

interface CodebaseAnalyzerInterface
{
    /**
     * Analyze the codebase at a specific commit.
     *
     * @return array<string, ClassInfo> Map of FQCN to ClassInfo
     */
    public function analyzeAtCommit(string $commitHash): array;
}
