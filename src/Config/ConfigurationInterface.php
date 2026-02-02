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

namespace Phauthentic\BcCheck\Config;

interface ConfigurationInterface
{
    /**
     * @return list<string>
     */
    public function getIncludePatterns(): array;

    /**
     * @return list<string>
     */
    public function getExcludePatterns(): array;

    /**
     * @return list<string>
     */
    public function getSourceDirectories(): array;

    /**
     * @return list<class-string>
     */
    public function getExternalDetectors(): array;

    public function shouldInclude(string $fqcn): bool;
}
