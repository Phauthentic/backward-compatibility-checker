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

final readonly class MethodInfo
{
    /**
     * @param list<ParameterInfo> $parameters
     */
    public function __construct(
        public string $name,
        public Visibility $visibility,
        public bool $isStatic = false,
        public bool $isFinal = false,
        public bool $isAbstract = false,
        public ?TypeInfo $returnType = null,
        public array $parameters = [],
    ) {
    }

    public function getSignature(): string
    {
        $parts = [];

        if ($this->isFinal) {
            $parts[] = 'final';
        }

        if ($this->isAbstract) {
            $parts[] = 'abstract';
        }

        $parts[] = $this->visibility->value;

        if ($this->isStatic) {
            $parts[] = 'static';
        }

        $parts[] = 'function';
        $parts[] = $this->name;

        $params = array_map(
            static fn (ParameterInfo $p): string => $p->toString(),
            $this->parameters,
        );

        $signature = implode(' ', $parts) . '(' . implode(', ', $params) . ')';

        if ($this->returnType !== null) {
            $signature .= ': ' . $this->returnType->toString();
        }

        return $signature;
    }

    public function getRequiredParameterCount(): int
    {
        $count = 0;
        foreach ($this->parameters as $param) {
            if (!$param->hasDefault && !$param->isVariadic) {
                $count++;
            }
        }

        return $count;
    }
}
