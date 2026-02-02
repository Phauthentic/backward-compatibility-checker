# BC Check - Backward Compatibility Checker for PHP

BC Check analyzes PHP code between two git commits to detect backward compatibility (BC) breaking changes using AST parsing. It helps library maintainers follow [Semantic Versioning](https://semver.org/) by identifying changes that could break dependent code.

> "With a system of the size and importance of API, it is necessary to have some formalized rules to govern how updates are incorporated while still keeping the API stable." — [Semantic Versioning 2.0.0](https://semver.org/)

## Features 💎

* **BC Break Detection:**
  * Detects removed public/protected classes, methods, properties, and constants
  * Detects method signature changes (parameters, types, defaults)
  * Detects return type changes
  * Detects visibility reductions
  * Detects added `final`, `abstract`, `static`, `readonly` modifiers
  * Detects interface implementation changes
  * Detects parent class changes
* **Flexible Configuration:**
  * YAML-based configuration
  * Regex patterns for include/exclude filtering
  * Custom source directories
  * External detector loading for custom BC break detection
* **Multiple Output Formats:**
  * Human-readable text output
  * JSON output for programmatic use
  * GitHub Actions annotations
  * SARIF (Static Analysis Results Interchange Format)
  * Checkstyle XML
  * JUnit XML
  * GitLab Code Quality
* **CI/CD Ready:**
  * Exit codes for build success/failure
  * GitHub Actions, GitLab CI, CircleCI, Jenkins examples
  * Docker support

## Installation ⚙️

```bash
composer require --dev phauthentic/bc-check
```

Or download the PHAR from the [releases page](https://github.com/Phauthentic/bc-check/releases).

## Running it 🧑‍💻

```bash
# Basic usage
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0

# With configuration file
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --config=bc-check.yaml

# JSON output
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=json

# GitHub Actions annotations
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=github-actions

# SARIF format (for GitHub, Azure DevOps, VS Code)
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=sarif

# Checkstyle XML (for PHPStan/Psalm-style tooling)
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=checkstyle

# JUnit XML (for CI/CD test result parsing)
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=junit

# GitLab Code Quality
vendor/bin/bc-check check /path/to/repo v1.0.0 v2.0.0 --format=gitlab
```

### Using Make

```bash
make test      # Run tests
make phpstan   # Run static analysis
make qa        # Run all quality checks
```

### Using Docker

```bash
make docker-build   # Build Docker image
make docker-shell   # Open shell in container
make docker-qa      # Run QA in container
```

## Documentation 📚

* [Installation](docs/Installation.md) - Install via Composer, PHAR, or Docker
* [Usage](docs/Usage.md) - CLI usage, examples, output formats
* [Configuration](docs/Configuration.md) - YAML config options, regex patterns
* [Detectors](docs/Detectors.md) - List of all detected BC breaks
* [Custom Detectors](docs/Custom-Detectors.md) - Create your own detectors
* [CI Integration](docs/CI-Integration.md) - GitHub Actions, GitLab CI, etc.

## Configuration 🔧

Create a `bc-check.yaml` file:

```yaml
# Patterns to include (regex matching FQCN)
include:
  - '^App\\Api\\.*'
  - '^App\\Service\\.*'

# Patterns to exclude
exclude:
  - '.*\\Internal\\.*'
  - '.*Test$'

# Source directories
source_directories:
  - src/

# External detector classes
external_detectors:
  - 'Vendor\\Custom\\MyDetector'
```

## BC Breaks Detected 🔍

### Class-Level

| Type | Description |
|------|-------------|
| `CLASS_REMOVED` | Public class/interface/trait was removed |
| `CLASS_MADE_FINAL` | Class was made final (breaks inheritance) |
| `CLASS_MADE_ABSTRACT` | Class was made abstract (breaks instantiation) |
| `INTERFACE_REMOVED` | Interface implementation was removed |
| `PARENT_CHANGED` | Parent class was changed or removed |

### Method-Level

| Type | Description |
|------|-------------|
| `METHOD_REMOVED` | Public/protected method was removed |
| `METHOD_SIGNATURE_CHANGED` | Parameters, types, or defaults changed |
| `METHOD_RETURN_TYPE_CHANGED` | Return type was changed |
| `METHOD_VISIBILITY_REDUCED` | Visibility reduced (public → protected/private) |
| `METHOD_MADE_FINAL` | Method was made final |
| `METHOD_MADE_STATIC` | Static modifier was added/removed |
| `METHOD_MADE_ABSTRACT` | Method was made abstract |

### Property-Level

| Type | Description |
|------|-------------|
| `PROPERTY_REMOVED` | Public/protected property was removed |
| `PROPERTY_VISIBILITY_REDUCED` | Visibility was reduced |
| `PROPERTY_TYPE_CHANGED` | Type was changed |
| `PROPERTY_MADE_READONLY` | Property was made readonly |
| `PROPERTY_MADE_STATIC` | Static modifier was added/removed |

### Constant-Level

| Type | Description |
|------|-------------|
| `CONSTANT_REMOVED` | Public/protected constant was removed |
| `CONSTANT_VISIBILITY_REDUCED` | Visibility was reduced |

## Example Output 📋

### Text Format

```
Found 3 BC break(s):

  ✗ [METHOD_REMOVED] Public method App\Service\UserService::getUser() was removed
  ✗ [METHOD_SIGNATURE_CHANGED] Method App\Service\UserService::createUser() has more required parameters (1 -> 2)
  ✗ [CLASS_MADE_FINAL] Class App\Entity\User was made final
```

### JSON Format

```json
{
    "total": 3,
    "breaks": [
        {
            "type": "METHOD_REMOVED",
            "message": "Public method App\\Service\\UserService::getUser() was removed",
            "class": "App\\Service\\UserService",
            "member": "getUser"
        }
    ]
}
```

### SARIF Format

SARIF (Static Analysis Results Interchange Format) is an OASIS standard supported by GitHub, Azure DevOps, and VS Code.

```json
{
    "$schema": "https://json.schemastore.org/sarif-2.1.0.json",
    "version": "2.1.0",
    "runs": [{
        "tool": {
            "driver": {
                "name": "php-bc-check",
                "version": "1.0.0",
                "rules": [{"id": "METHOD_REMOVED", "shortDescription": {"text": "..."}}]
            }
        },
        "results": [{
            "ruleId": "METHOD_REMOVED",
            "level": "error",
            "message": {"text": "Public method App\\Service\\UserService::getUser() was removed"}
        }]
    }]
}
```

### Checkstyle XML Format

Standard Checkstyle format supported by many CI tools and IDEs.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<checkstyle version="1.0.0">
  <file name="App\Service\UserService">
    <error severity="error" message="Public method getUser() was removed" source="bc-check.METHOD_REMOVED"/>
  </file>
</checkstyle>
```

### JUnit XML Format

JUnit format for CI/CD systems like Jenkins, GitLab CI, and CircleCI.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="BC Check" tests="1" failures="1" errors="0">
    <testcase name="App\Service\UserService::getUser" classname="METHOD_REMOVED">
      <failure message="Public method getUser() was removed" type="METHOD_REMOVED"/>
    </testcase>
  </testsuite>
</testsuites>
```

### GitLab Code Quality Format

GitLab Code Quality format for inline MR annotations.

```json
[
    {
        "description": "Public method getUser() was removed",
        "check_name": "METHOD_REMOVED",
        "fingerprint": "abc123...",
        "severity": "major",
        "location": {"path": "App/Service/UserService.php", "lines": {"begin": 1}}
    }
]
```

## Custom Detectors 🔌

Create custom detectors by implementing `BcBreakDetectorInterface`:

```php
<?php

use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\ClassInfo;

final readonly class MyCustomDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        $breaks = [];
        // Your custom detection logic
        return $breaks;
    }
}
```

Register in configuration:

```yaml
external_detectors:
  - 'Vendor\\Custom\\MyCustomDetector'
```

See [Custom Detectors](docs/Custom-Detectors.md) for full documentation.

## CI/CD Integration 🚀

### GitHub Actions

```yaml
- name: Run BC Check
  run: |
    BASE_SHA=$(git merge-base origin/${{ github.base_ref }} HEAD)
    vendor/bin/bc-check check . $BASE_SHA HEAD --format=github-actions
```

#### GitHub Actions with SARIF Upload

```yaml
- name: Run BC Check (SARIF)
  run: |
    BASE_SHA=$(git merge-base origin/${{ github.base_ref }} HEAD)
    vendor/bin/bc-check check . $BASE_SHA HEAD --format=sarif > bc-check.sarif
  continue-on-error: true

- name: Upload SARIF
  uses: github/codeql-action/upload-sarif@v3
  with:
    sarif_file: bc-check.sarif
```

### GitLab CI

```yaml
bc-check:
  script:
    - vendor/bin/bc-check check . $CI_MERGE_REQUEST_DIFF_BASE_SHA HEAD --format=gitlab > gl-code-quality-report.json
  artifacts:
    reports:
      codequality: gl-code-quality-report.json
```

### Jenkins / CircleCI (JUnit)

```yaml
- run:
    name: BC Check
    command: vendor/bin/bc-check check . $BASE_SHA HEAD --format=junit > bc-check-results.xml
- store_test_results:
    path: bc-check-results.xml
```

See [CI Integration](docs/CI-Integration.md) for more examples.

## Development 🛠️

```bash
# Install dependencies
make install

# Run tests
make test

# Run PHPStan
make phpstan

# Fix code style
make cs-fix

# Run all quality checks
make qa
```

## Reporting Issues 🪲

If you find a bug or have a feature request, please open an issue on the [GitHub repository](https://github.com/Phauthentic/bc-check/issues/new).

When reporting issues, please provide:

- PHP version
- BC Check version
- Minimal code example that reproduces the issue
- Expected vs actual behavior

## License ⚖️

Copyright Florian Krämer

Licensed under the [GNU General Public License v3.0](LICENSE).
