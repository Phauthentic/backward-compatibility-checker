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

namespace Phauthentic\BcCheck\ValueObject;

final readonly class PropertyInfo
{
    public function __construct(
        public string $name,
        public Visibility $visibility,
        public ?TypeInfo $type = null,
        public bool $isStatic = false,
        public bool $isReadonly = false,
        public bool $hasDefault = false,
    ) {
    }

    public function getSignature(): string
    {
        $parts = [];

        if ($this->isReadonly) {
            $parts[] = 'readonly';
        }

        $parts[] = $this->visibility->value;

        if ($this->isStatic) {
            $parts[] = 'static';
        }

        if ($this->type !== null) {
            $parts[] = $this->type->toString();
        }

        $parts[] = '$' . $this->name;

        return implode(' ', $parts);
    }
}
