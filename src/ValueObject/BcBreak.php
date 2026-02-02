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

final readonly class BcBreak
{
    public function __construct(
        public string $message,
        public string $className,
        public ?string $memberName = null,
        public BcBreakType $type = BcBreakType::Other,
    ) {
    }

    public function getFullIdentifier(): string
    {
        if ($this->memberName !== null) {
            return $this->className . '::' . $this->memberName;
        }

        return $this->className;
    }

    public function toString(): string
    {
        return sprintf('[BC] %s: %s', $this->type->value, $this->message);
    }
}
