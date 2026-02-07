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

final readonly class ConstantInfo
{
    public function __construct(
        public string $name,
        public Visibility $visibility,
        public bool $isFinal = false,
        public ?TypeInfo $type = null,
    ) {
    }

    public function getSignature(): string
    {
        $parts = [];

        if ($this->isFinal) {
            $parts[] = 'final';
        }

        $parts[] = $this->visibility->value;
        $parts[] = 'const';

        if ($this->type !== null) {
            $parts[] = $this->type->toString();
        }

        $parts[] = $this->name;

        return implode(' ', $parts);
    }
}
