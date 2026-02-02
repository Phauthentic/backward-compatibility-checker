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

namespace Phauthentic\BcCheck\Parser;

use Phauthentic\BcCheck\ValueObject\ClassInfo;
use Phauthentic\BcCheck\ValueObject\ClassType;
use Phauthentic\BcCheck\ValueObject\ConstantInfo;
use Phauthentic\BcCheck\ValueObject\MethodInfo;
use Phauthentic\BcCheck\ValueObject\ParameterInfo;
use Phauthentic\BcCheck\ValueObject\PropertyInfo;
use Phauthentic\BcCheck\ValueObject\TypeInfo;
use Phauthentic\BcCheck\ValueObject\Visibility;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

final class ClassAnalyzer extends NodeVisitorAbstract
{
    /** @var list<ClassInfo> */
    private array $classes = [];

    private ?string $currentNamespace = null;

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node->name?->toString();

            return null;
        }

        if ($node instanceof Class_ && $node->name !== null) {
            $this->classes[] = $this->analyzeClass($node);

            return NodeVisitorAbstract::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Interface_) {
            $this->classes[] = $this->analyzeInterface($node);

            return NodeVisitorAbstract::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Trait_) {
            $this->classes[] = $this->analyzeTrait($node);

            return NodeVisitorAbstract::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Enum_) {
            $this->classes[] = $this->analyzeEnum($node);

            return NodeVisitorAbstract::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    public function leaveNode(Node $node): ?int
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = null;
        }

        return null;
    }

    /**
     * @return list<ClassInfo>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    private function analyzeClass(Class_ $node): ClassInfo
    {
        assert($node->name !== null);
        $name = $this->getFullyQualifiedName($node->name->toString());

        return new ClassInfo(
            name: $name,
            type: ClassType::ClassType,
            isFinal: $node->isFinal(),
            isAbstract: $node->isAbstract(),
            isReadonly: $node->isReadonly(),
            parentClass: $node->extends?->toString(),
            interfaces: array_values(array_map(
                static fn ($interface): string => $interface->toString(),
                $node->implements,
            )),
            traits: $this->extractTraitUses($node),
            methods: $this->extractMethods($node->getMethods()),
            properties: $this->extractProperties($node->getProperties()),
            constants: $this->extractConstants(array_values($node->stmts)),
        );
    }

    private function analyzeInterface(Interface_ $node): ClassInfo
    {
        assert($node->name !== null);
        $name = $this->getFullyQualifiedName($node->name->toString());

        return new ClassInfo(
            name: $name,
            type: ClassType::Interface,
            interfaces: array_values(array_map(
                static fn ($interface): string => $interface->toString(),
                $node->extends,
            )),
            methods: $this->extractMethods($node->getMethods()),
            constants: $this->extractConstants(array_values($node->stmts)),
        );
    }

    private function analyzeTrait(Trait_ $node): ClassInfo
    {
        assert($node->name !== null);
        $name = $this->getFullyQualifiedName($node->name->toString());

        return new ClassInfo(
            name: $name,
            type: ClassType::Trait,
            methods: $this->extractMethods($node->getMethods()),
            properties: $this->extractProperties($node->getProperties()),
        );
    }

    private function analyzeEnum(Enum_ $node): ClassInfo
    {
        assert($node->name !== null);
        $name = $this->getFullyQualifiedName($node->name->toString());

        return new ClassInfo(
            name: $name,
            type: ClassType::Enum,
            isFinal: true, // Enums are implicitly final
            interfaces: array_values(array_map(
                static fn ($interface): string => $interface->toString(),
                $node->implements,
            )),
            methods: $this->extractMethods($node->getMethods()),
            constants: $this->extractConstants(array_values($node->stmts)),
        );
    }

    private function getFullyQualifiedName(string $name): string
    {
        if ($this->currentNamespace !== null) {
            return $this->currentNamespace . '\\' . $name;
        }

        return $name;
    }

    /**
     * @param list<ClassMethod> $methods
     * @return list<MethodInfo>
     */
    private function extractMethods(array $methods): array
    {
        $result = [];

        foreach ($methods as $method) {
            $result[] = new MethodInfo(
                name: $method->name->toString(),
                visibility: $this->getVisibility($method),
                isStatic: $method->isStatic(),
                isFinal: $method->isFinal(),
                isAbstract: $method->isAbstract(),
                returnType: $this->extractType($method->returnType),
                parameters: $this->extractParameters(array_values($method->params)),
            );
        }

        return $result;
    }

    /**
     * @param list<Property> $properties
     * @return list<PropertyInfo>
     */
    private function extractProperties(array $properties): array
    {
        $result = [];

        foreach ($properties as $property) {
            foreach ($property->props as $prop) {
                $result[] = new PropertyInfo(
                    name: $prop->name->toString(),
                    visibility: $this->getPropertyVisibility($property),
                    type: $this->extractType($property->type),
                    isStatic: $property->isStatic(),
                    isReadonly: $property->isReadonly(),
                    hasDefault: $prop->default !== null,
                );
            }
        }

        return $result;
    }

    /**
     * @param list<Node\Stmt> $stmts
     * @return list<ConstantInfo>
     */
    private function extractConstants(array $stmts): array
    {
        $result = [];

        foreach ($stmts as $stmt) {
            if (!$stmt instanceof ClassConst) {
                continue;
            }

            $visibility = Visibility::Public;
            if ($stmt->isPrivate()) {
                $visibility = Visibility::Private;
            } elseif ($stmt->isProtected()) {
                $visibility = Visibility::Protected;
            }

            foreach ($stmt->consts as $const) {
                $result[] = new ConstantInfo(
                    name: $const->name->toString(),
                    visibility: $visibility,
                    isFinal: $stmt->isFinal(),
                    type: $this->extractType($stmt->type),
                );
            }
        }

        return $result;
    }

    /**
     * @param list<Node\Param> $params
     * @return list<ParameterInfo>
     */
    private function extractParameters(array $params): array
    {
        $result = [];

        foreach ($params as $param) {
            $result[] = new ParameterInfo(
                name: $param->var instanceof Node\Expr\Variable && is_string($param->var->name)
                    ? $param->var->name
                    : 'unknown',
                type: $this->extractType($param->type),
                hasDefault: $param->default !== null,
                isVariadic: $param->variadic,
                isByReference: $param->byRef,
                isPromoted: $param->flags !== 0,
            );
        }

        return $result;
    }

    private function extractType(?Node $type): ?TypeInfo
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof Node\Identifier) {
            return new TypeInfo($type->toString());
        }

        if ($type instanceof Node\Name) {
            return new TypeInfo($type->toString());
        }

        if ($type instanceof Node\NullableType) {
            $innerType = $this->extractType($type->type);

            return new TypeInfo(
                name: $innerType->name ?? 'mixed',
                isNullable: true,
            );
        }

        if ($type instanceof Node\UnionType) {
            $types = [];
            foreach ($type->types as $t) {
                $extracted = $this->extractType($t);
                if ($extracted !== null) {
                    $types[] = $extracted->name;
                }
            }

            return new TypeInfo(
                name: implode('|', $types),
                isNullable: in_array('null', $types, true),
                isUnion: true,
                types: $types,
            );
        }

        if ($type instanceof Node\IntersectionType) {
            $types = [];
            foreach ($type->types as $t) {
                $extracted = $this->extractType($t);
                if ($extracted !== null) {
                    $types[] = $extracted->name;
                }
            }

            return new TypeInfo(
                name: implode('&', $types),
                isIntersection: true,
                types: $types,
            );
        }

        return null;
    }

    private function getVisibility(ClassMethod $method): Visibility
    {
        if ($method->isPrivate()) {
            return Visibility::Private;
        }

        if ($method->isProtected()) {
            return Visibility::Protected;
        }

        return Visibility::Public;
    }

    private function getPropertyVisibility(Property $property): Visibility
    {
        if ($property->isPrivate()) {
            return Visibility::Private;
        }

        if ($property->isProtected()) {
            return Visibility::Protected;
        }

        return Visibility::Public;
    }

    /**
     * @return list<string>
     */
    private function extractTraitUses(Class_|Trait_ $node): array
    {
        $traits = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\TraitUse) {
                foreach ($stmt->traits as $trait) {
                    $traits[] = $trait->toString();
                }
            }
        }

        return $traits;
    }
}
