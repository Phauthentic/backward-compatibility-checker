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

enum Visibility: string
{
    public function isMoreRestrictiveThan(self $other): bool
    {
        return match ([$this, $other]) {
            [self::Private, self::Protected],
            [self::Private, self::Public],
            [self::Protected, self::Public] => true,
            default => false,
        };
    }

    public function isAccessibleFrom(self $context): bool
    {
        return match ($this) {
            self::Public => true,
            self::Protected => $context !== self::Public,
            self::Private => $context === self::Private,
        };
    }
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';
}
