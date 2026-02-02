# Detectors

BC Check includes built-in detectors for common backward compatibility breaking changes.

## Class-Level Detectors

| Detector | Type | Description |
|----------|------|-------------|
| ClassRemovedDetector | `CLASS_REMOVED` | Public class, interface, or trait was removed |
| ClassMadeFinalDetector | `CLASS_MADE_FINAL` | Class was made final (breaks inheritance) |
| ClassMadeAbstractDetector | `CLASS_MADE_ABSTRACT` | Class was made abstract (breaks instantiation) |
| InterfaceRemovedDetector | `INTERFACE_REMOVED` | Interface implementation was removed |
| ParentChangedDetector | `PARENT_CHANGED` | Parent class was changed or removed |

### Examples

```php
// Before
class UserService {}

// After - CLASS_MADE_FINAL
final class UserService {}
```

```php
// Before
class UserService implements LoggerAwareInterface {}

// After - INTERFACE_REMOVED
class UserService {}
```

## Method-Level Detectors

| Detector | Type | Description |
|----------|------|-------------|
| MethodRemovedDetector | `METHOD_REMOVED` | Public or protected method was removed |
| MethodSignatureChangedDetector | `METHOD_SIGNATURE_CHANGED` | Method parameters changed (count, types, defaults) |
| MethodReturnTypeChangedDetector | `METHOD_RETURN_TYPE_CHANGED` | Method return type was changed |
| MethodVisibilityReducedDetector | `METHOD_VISIBILITY_REDUCED` | Method visibility was reduced |
| MethodMadeFinalDetector | `METHOD_MADE_FINAL` | Method was made final |
| MethodMadeStaticDetector | `METHOD_MADE_STATIC` / `METHOD_MADE_NON_STATIC` | Static modifier changed |
| MethodMadeAbstractDetector | `METHOD_MADE_ABSTRACT` | Method was made abstract |

### Examples

```php
// Before
public function create(string $name): User {}

// After - METHOD_SIGNATURE_CHANGED (added required parameter)
public function create(string $name, string $email): User {}
```

```php
// Before
public function getUser(): User {}

// After - METHOD_RETURN_TYPE_CHANGED
public function getUser(): ?User {}
```

## Property-Level Detectors

| Detector | Type | Description |
|----------|------|-------------|
| PropertyRemovedDetector | `PROPERTY_REMOVED` | Public or protected property was removed |
| PropertyVisibilityReducedDetector | `PROPERTY_VISIBILITY_REDUCED` | Property visibility was reduced |
| PropertyTypeChangedDetector | `PROPERTY_TYPE_CHANGED` | Property type was changed |
| PropertyMadeReadonlyDetector | `PROPERTY_MADE_READONLY` | Property was made readonly |
| PropertyMadeStaticDetector | `PROPERTY_MADE_STATIC` / `PROPERTY_MADE_NON_STATIC` | Static modifier changed |

### Examples

```php
// Before
public string $name;

// After - PROPERTY_VISIBILITY_REDUCED
protected string $name;
```

```php
// Before
public string $name;

// After - PROPERTY_MADE_READONLY
public readonly string $name;
```

## Constant-Level Detectors

| Detector | Type | Description |
|----------|------|-------------|
| ConstantRemovedDetector | `CONSTANT_REMOVED` | Public or protected constant was removed |
| ConstantVisibilityReducedDetector | `CONSTANT_VISIBILITY_REDUCED` | Constant visibility was reduced |

### Examples

```php
// Before
public const VERSION = '1.0.0';

// After - CONSTANT_VISIBILITY_REDUCED
protected const VERSION = '1.0.0';
```

## BC Break Types Summary

| Type | Severity | Description |
|------|----------|-------------|
| `CLASS_REMOVED` | Critical | Class no longer exists |
| `CLASS_MADE_FINAL` | Major | Cannot extend class |
| `CLASS_MADE_ABSTRACT` | Major | Cannot instantiate class |
| `INTERFACE_REMOVED` | Major | Type hint compatibility broken |
| `PARENT_CHANGED` | Major | Inheritance chain changed |
| `METHOD_REMOVED` | Critical | Method no longer exists |
| `METHOD_SIGNATURE_CHANGED` | Major | Method call compatibility broken |
| `METHOD_RETURN_TYPE_CHANGED` | Major | Return type compatibility broken |
| `METHOD_VISIBILITY_REDUCED` | Major | Cannot access method |
| `METHOD_MADE_FINAL` | Minor | Cannot override method |
| `METHOD_MADE_STATIC` | Major | Call syntax changed |
| `METHOD_MADE_ABSTRACT` | Major | Must implement method |
| `PROPERTY_REMOVED` | Critical | Property no longer exists |
| `PROPERTY_VISIBILITY_REDUCED` | Major | Cannot access property |
| `PROPERTY_TYPE_CHANGED` | Major | Type compatibility broken |
| `PROPERTY_MADE_READONLY` | Minor | Cannot modify property |
| `CONSTANT_REMOVED` | Critical | Constant no longer exists |
| `CONSTANT_VISIBILITY_REDUCED` | Major | Cannot access constant |

## Next Steps

- [Custom Detectors](Custom-Detectors.md) - Create your own detectors
- [Configuration](Configuration.md) - Filter which classes are analyzed
