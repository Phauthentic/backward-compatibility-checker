# Usage

BC Check analyzes PHP code between two git commits to detect backward compatibility breaking changes.

## Basic Usage

```bash
bc-check check <repository> <from-commit> <to-commit>
```

### Arguments

| Argument | Description |
|----------|-------------|
| `repository` | Path to the git repository |
| `from-commit` | The base commit hash (older version) |
| `to-commit` | The target commit hash (newer version) |

### Examples

```bash
# Compare two commits
bc-check check /path/to/repo abc123 def456

# Compare tags
bc-check check /path/to/repo v1.0.0 v2.0.0

# Compare a tag to HEAD
bc-check check /path/to/repo v1.0.0 HEAD

# Compare branches
bc-check check /path/to/repo main feature-branch
```

## Options

### Configuration File

Use a custom configuration file:

```bash
bc-check check /path/to/repo v1.0.0 v2.0.0 --config=bc-check.yaml
```

If no config file is specified, BC Check looks for these files in order:
1. `bc-check.yaml`
2. `bc-check.yml`
3. `bc-check.yaml.dist`

### Show Files Being Processed

Use `--show-files` to display which files are being analyzed:

```bash
bc-check check /path/to/repo v1.0.0 v2.0.0 --show-files
```

Output:

```
Processing (source): src/Service/UserService.php
Processing (source): src/Entity/User.php
Processing (target): src/Service/UserService.php
Processing (target): src/Entity/User.php

  ✓ No backward compatibility breaks detected!

Time: 0.12s
```

The `(source)` label indicates files from the "from" commit (older version), while `(target)` indicates files from the "to" commit (newer version).

### Execution Timing

BC Check always displays execution time at the end of the output:

```
Time: 0.52s
```

### Output Formats

BC Check supports multiple output formats:

```bash
# Human-readable text (default)
bc-check check /path/to/repo v1.0.0 v2.0.0 --format=text

# JSON for programmatic use
bc-check check /path/to/repo v1.0.0 v2.0.0 --format=json

# GitHub Actions annotations
bc-check check /path/to/repo v1.0.0 v2.0.0 --format=github-actions
```

#### Text Output

```
Found 3 BC break(s):

  ✗ [METHOD_REMOVED] Public method App\Service\UserService::getUser() was removed
  ✗ [METHOD_SIGNATURE_CHANGED] Method App\Service\UserService::createUser() has more required parameters (1 -> 2)
  ✗ [CLASS_MADE_FINAL] Class App\Entity\User was made final

Time: 0.52s
```

#### JSON Output

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

#### GitHub Actions Output

```
::error title=METHOD_REMOVED::Public method App\Service\UserService::getUser() was removed
::error::Found 3 BC break(s)
```

## Exit Codes

| Code | Description |
|------|-------------|
| 0 | No BC breaks found |
| 1 | BC breaks detected |
| 1 | Error occurred (invalid commits, config error, etc.) |

## Using with Makefile

If you've installed BC Check in your project, you can add it to your Makefile:

```makefile
.PHONY: bc-check

bc-check:
	vendor/bin/bc-check check . $(shell git describe --tags --abbrev=0) HEAD
```

Then run:

```bash
make bc-check
```

## Next Steps

- [Configuration](Configuration.md) - Configure filtering and patterns
- [Detectors](Detectors.md) - See all detected BC breaks
- [CI Integration](CI-Integration.md) - Integrate with CI/CD pipelines
