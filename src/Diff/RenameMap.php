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

namespace Phauthentic\BcCheck\Diff;

final readonly class RenameMap
{
    /**
     * @param array<string, string> $methodRenames oldName => newName
     * @param array<string, string> $propertyRenames oldName => newName
     */
    public function __construct(
        private array $methodRenames = [],
        private array $propertyRenames = [],
    ) {
    }

    public function getMethodNewName(string $oldName): ?string
    {
        return $this->methodRenames[$oldName] ?? null;
    }

    public function getPropertyNewName(string $oldName): ?string
    {
        return $this->propertyRenames[$oldName] ?? null;
    }

    public function hasMethodRenames(): bool
    {
        return $this->methodRenames !== [];
    }

    public function hasPropertyRenames(): bool
    {
        return $this->propertyRenames !== [];
    }
}
