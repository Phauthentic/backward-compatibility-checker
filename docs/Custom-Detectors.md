# Custom Detectors

BC Check supports loading external detector classes via configuration, allowing you to add custom BC break detection logic.

## Creating a Custom Detector

### 1. Implement the Interface

Create a class that implements `Phauthentic\BcCheck\Detector\BcBreakDetectorInterface`:

```php
<?php

declare(strict_types=1);

namespace Vendor\Custom;

use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;

final readonly class DeprecatedMethodDetector implements BcBreakDetectorInterface
{
    /**
     * @return list<BcBreak>
     */
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        // Your custom detection logic here
        foreach ($before->getPublicOrProtectedMethods() as $method) {
            // Example: detect if a method was removed that had @deprecated
            $afterMethod = $after->getMethod($method->name);
            
            if ($afterMethod === null) {
                // Method was removed - you could check for deprecation here
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Method %s::%s() was removed',
                        $before->name,
                        $method->name,
                    ),
                    className: $before->name,
                    memberName: $method->name,
                    type: BcBreakType::MethodRemoved,
                );
            }
        }

        return $breaks;
    }
}
```

### 2. Register the Detector

Add your detector class to the configuration:

```yaml
# bc-check.yaml
external_detectors:
  - 'Vendor\\Custom\\DeprecatedMethodDetector'
```

### 3. Ensure Autoloading

Make sure your detector class is autoloadable. If it's in a separate package:

```json
{
    "require-dev": {
        "vendor/custom-detectors": "^1.0"
    }
}
```

Or add it to your project's autoload:

```json
{
    "autoload": {
        "psr-4": {
            "Vendor\\Custom\\": "custom-detectors/"
        }
    }
}
```

## Available Value Objects

### ClassInfo

Contains class metadata:

```php
$before->name;              // string - FQCN
$before->type;              // ClassType enum (ClassType, Interface, Trait, Enum)
$before->isFinal;           // bool
$before->isAbstract;        // bool
$before->isReadonly;        // bool
$before->parentClass;       // ?string
$before->interfaces;        // list<string>
$before->methods;           // list<MethodInfo>
$before->properties;        // list<PropertyInfo>
$before->constants;         // list<ConstantInfo>

// Helper methods
$before->getMethod('name');                    // ?MethodInfo
$before->getProperty('name');                  // ?PropertyInfo
$before->getConstant('name');                  // ?ConstantInfo
$before->getPublicOrProtectedMethods();        // list<MethodInfo>
$before->getPublicOrProtectedProperties();     // list<PropertyInfo>
$before->getPublicOrProtectedConstants();      // list<ConstantInfo>
```

### MethodInfo

Contains method metadata:

```php
$method->name;          // string
$method->visibility;    // Visibility enum (Public, Protected, Private)
$method->isStatic;      // bool
$method->isFinal;       // bool
$method->isAbstract;    // bool
$method->returnType;    // ?TypeInfo
$method->parameters;    // list<ParameterInfo>
```

### PropertyInfo

Contains property metadata:

```php
$property->name;        // string
$property->visibility;  // Visibility enum
$property->type;        // ?TypeInfo
$property->isStatic;    // bool
$property->isReadonly;  // bool
$property->hasDefault;  // bool
```

### ParameterInfo

Contains parameter metadata:

```php
$param->name;           // string
$param->type;           // ?TypeInfo
$param->hasDefault;     // bool
$param->isVariadic;     // bool
$param->isByReference;  // bool
$param->isPromoted;     // bool
```

### BcBreak

The result object:

```php
new BcBreak(
    message: 'Human-readable description',
    className: 'App\\Service\\UserService',
    memberName: 'methodName',  // optional
    type: BcBreakType::MethodRemoved,
);
```

### BcBreakType

Available break types:

```php
BcBreakType::ClassRemoved
BcBreakType::ClassMadeFinal
BcBreakType::ClassMadeAbstract
BcBreakType::InterfaceRemoved
BcBreakType::ParentChanged
BcBreakType::MethodRemoved
BcBreakType::MethodSignatureChanged
BcBreakType::MethodReturnTypeChanged
BcBreakType::MethodVisibilityReduced
BcBreakType::MethodMadeFinal
BcBreakType::MethodMadeStatic
BcBreakType::MethodMadeNonStatic
BcBreakType::MethodMadeAbstract
BcBreakType::PropertyRemoved
BcBreakType::PropertyVisibilityReduced
BcBreakType::PropertyTypeChanged
BcBreakType::PropertyMadeReadonly
BcBreakType::PropertyMadeStatic
BcBreakType::PropertyMadeNonStatic
BcBreakType::ConstantRemoved
BcBreakType::ConstantVisibilityReduced
BcBreakType::Other  // For custom break types
```

## Example: Doctrine Annotation Detector

Here's a more complex example that detects removed Doctrine annotations:

```php
<?php

declare(strict_types=1);

namespace Vendor\Custom;

use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Phauthentic\BcCheck\ValueObject\ClassInfo;

final readonly class DoctrineEntityChangeDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];

        // Only check Entity classes (you'd need to determine this somehow)
        if (!str_ends_with($before->name, 'Entity')) {
            return [];
        }

        // Detect removed properties (columns)
        foreach ($before->getPublicOrProtectedProperties() as $prop) {
            if ($after->getProperty($prop->name) === null) {
                $breaks[] = new BcBreak(
                    message: sprintf(
                        'Entity column %s::$%s was removed (potential data loss)',
                        $before->name,
                        $prop->name,
                    ),
                    className: $before->name,
                    memberName: $prop->name,
                    type: BcBreakType::Other,
                );
            }
        }

        return $breaks;
    }
}
```

## Best Practices

1. **Single Responsibility**: Each detector should focus on one type of BC break
2. **Clear Messages**: Write descriptive messages that explain the impact
3. **Use Appropriate Types**: Use `BcBreakType::Other` for custom break types
4. **Return Empty Arrays**: Return `[]` when no breaks are found
5. **Handle Edge Cases**: Check for null values and missing data

## Next Steps

- [Detectors](Detectors.md) - See all built-in detectors
- [Configuration](Configuration.md) - Configure BC Check
