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

final readonly class ClassInfo
{
    /**
     * @param list<MethodInfo> $methods
     * @param list<PropertyInfo> $properties
     * @param list<ConstantInfo> $constants
     * @param list<string> $interfaces
     * @param list<string> $traits
     */
    public function __construct(
        public string $name,
        public ClassType $type = ClassType::ClassType,
        public bool $isFinal = false,
        public bool $isAbstract = false,
        public bool $isReadonly = false,
        public ?string $parentClass = null,
        public array $interfaces = [],
        public array $traits = [],
        public array $methods = [],
        public array $properties = [],
        public array $constants = [],
    ) {
    }

    public function getMethod(string $name): ?MethodInfo
    {
        foreach ($this->methods as $method) {
            if ($method->name === $name) {
                return $method;
            }
        }

        return null;
    }

    public function getProperty(string $name): ?PropertyInfo
    {
        foreach ($this->properties as $property) {
            if ($property->name === $name) {
                return $property;
            }
        }

        return null;
    }

    public function getConstant(string $name): ?ConstantInfo
    {
        foreach ($this->constants as $constant) {
            if ($constant->name === $name) {
                return $constant;
            }
        }

        return null;
    }

    public function hasInterface(string $interface): bool
    {
        return in_array($interface, $this->interfaces, true);
    }

    /**
     * @return list<MethodInfo>
     */
    public function getPublicMethods(): array
    {
        return array_values(array_filter(
            $this->methods,
            static fn (MethodInfo $m): bool => $m->visibility === Visibility::Public,
        ));
    }

    /**
     * @return list<MethodInfo>
     */
    public function getPublicOrProtectedMethods(): array
    {
        return array_values(array_filter(
            $this->methods,
            static fn (MethodInfo $m): bool => $m->visibility !== Visibility::Private,
        ));
    }

    /**
     * @return list<PropertyInfo>
     */
    public function getPublicOrProtectedProperties(): array
    {
        return array_values(array_filter(
            $this->properties,
            static fn (PropertyInfo $p): bool => $p->visibility !== Visibility::Private,
        ));
    }

    /**
     * @return list<ConstantInfo>
     */
    public function getPublicOrProtectedConstants(): array
    {
        return array_values(array_filter(
            $this->constants,
            static fn (ConstantInfo $c): bool => $c->visibility !== Visibility::Private,
        ));
    }
}
