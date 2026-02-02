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

final readonly class TypeInfo
{
    /**
     * @param list<string> $types For union/intersection types
     */
    public function __construct(
        public string $name,
        public bool $isNullable = false,
        public bool $isUnion = false,
        public bool $isIntersection = false,
        public array $types = [],
    ) {
    }

    public function toString(): string
    {
        if ($this->isUnion && $this->types !== []) {
            return implode('|', $this->types);
        }

        if ($this->isIntersection && $this->types !== []) {
            return implode('&', $this->types);
        }

        if ($this->isNullable && $this->name !== 'mixed' && $this->name !== 'null') {
            return '?' . $this->name;
        }

        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }

    public static function fromString(string $type): self
    {
        if ($type === '') {
            return new self('mixed');
        }

        $isNullable = str_starts_with($type, '?');
        if ($isNullable) {
            $type = substr($type, 1);
        }

        if (str_contains($type, '|')) {
            $types = explode('|', $type);

            return new self(
                name: $type,
                isNullable: in_array('null', $types, true),
                isUnion: true,
                types: $types,
            );
        }

        if (str_contains($type, '&')) {
            $types = explode('&', $type);

            return new self(
                name: $type,
                isIntersection: true,
                types: $types,
            );
        }

        return new self(
            name: $type,
            isNullable: $isNullable,
        );
    }
}
