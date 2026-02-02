# Configuration

BC Check can be configured using a YAML configuration file.

## Configuration File

Create a `bc-check.yaml` file in your project root:

```yaml
# Patterns to include (regex matching FQCN)
include:
  - '^App\\Api\\.*'
  - '^App\\Service\\.*'

# Patterns to exclude (regex matching FQCN)
exclude:
  - '.*\\Internal\\.*'
  - '.*Test$'
  - '.*TestCase$'

# Source directories to scan
source_directories:
  - src/

# External detector classes
external_detectors:
  - 'Vendor\\Custom\\MyDetector'
```

## Options

### include

List of regex patterns to include classes for analysis. If empty, all classes are included.

```yaml
include:
  - '^App\\Api\\.*'           # All classes in App\Api namespace
  - '^App\\Service\\.*'       # All classes in App\Service namespace
  - '^App\\Entity\\User$'     # Specific class
```

### exclude

List of regex patterns to exclude classes from analysis. Exclusions take precedence over inclusions.

```yaml
exclude:
  - '.*\\Internal\\.*'        # Internal namespaces
  - '.*Test$'                 # Test classes
  - '.*TestCase$'             # TestCase classes
  - '.*\\Fixtures\\.*'        # Fixture classes
```

### source_directories

List of directories to scan for PHP files (relative to repository root).

```yaml
source_directories:
  - src/
  - lib/
```

Default: `['src/']`

### external_detectors

List of custom detector classes to load. See [Custom Detectors](Custom-Detectors.md) for details.

```yaml
external_detectors:
  - 'Vendor\\Custom\\MyDetector'
  - 'Vendor\\Custom\\AnotherDetector'
```

## Pattern Matching

Patterns are matched against the Fully Qualified Class Name (FQCN) using PHP's `preg_match()`.

### Examples

| Pattern | Matches |
|---------|---------|
| `^App\\\\.*` | All classes starting with `App\` |
| `.*Service$` | All classes ending with `Service` |
| `^App\\\\Entity\\\\.*` | All classes in `App\Entity` namespace |
| `.*\\\\Internal\\\\.*` | Any class with `Internal` in namespace |

### Note on Escaping

In YAML, backslashes must be escaped. Use `\\\\` for a single backslash in the regex pattern.

```yaml
# Match App\Service\*
include:
  - '^App\\\\Service\\\\.*'
```

## Default Configuration

If no configuration file is found, BC Check uses these defaults:

```yaml
include: []              # Include all classes
exclude: []              # Exclude nothing
source_directories:
  - src/
external_detectors: []   # No external detectors
```

## Command Line Override

You can specify a custom config file path:

```bash
bc-check check /path/to/repo v1.0.0 v2.0.0 --config=/path/to/config.yaml
```

## Next Steps

- [Detectors](Detectors.md) - See all detected BC breaks
- [Custom Detectors](Custom-Detectors.md) - Create your own detectors
