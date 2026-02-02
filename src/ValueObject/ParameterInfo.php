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

namespace Phauthentic\BcCheck\ValueObject;

final readonly class ParameterInfo
{
    public function __construct(
        public string $name,
        public ?TypeInfo $type = null,
        public bool $hasDefault = false,
        public bool $isVariadic = false,
        public bool $isByReference = false,
        public bool $isPromoted = false,
    ) {
    }

    public function toString(): string
    {
        $parts = [];

        if ($this->type !== null) {
            $parts[] = $this->type->toString();
        }

        if ($this->isByReference) {
            $parts[] = '&';
        }

        if ($this->isVariadic) {
            $parts[] = '...';
        }

        $parts[] = '$' . $this->name;

        if ($this->hasDefault) {
            $parts[] = '= ...';
        }

        return implode('', $parts);
    }

    public function isCompatibleWith(self $other): bool
    {
        // If previous had no type, new can have any type
        if ($this->type === null) {
            return true;
        }

        // If previous had type but new doesn't, it's a BC break
        if ($other->type === null) {
            return false;
        }

        // Type must be the same or contravariant (less restrictive)
        // For simplicity, we just check equality
        return $this->type->equals($other->type);
    }
}
