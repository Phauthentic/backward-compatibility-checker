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
namespace Fixtures;

// BC breaks:
// - class made final
// - getName() made final
// - setName() removed
// - increment() visibility reduced to private
// - count property type changed
// - VERSION constant visibility reduced

final class SimpleClass
{
    private const VERSION = '2.0.0';

    protected const INTERNAL = 'internal';

    public string $name;

    protected string $count = '0';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    private function increment(): void
    {
        $this->count = (string) ((int) $this->count + 1);
    }

    public function getCount(): int
    {
        return (int) $this->count;
    }
}
