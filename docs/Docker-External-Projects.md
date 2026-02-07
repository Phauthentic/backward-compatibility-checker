# Running BC Check via Docker Against External Projects

This guide explains how to use BC Check via Docker to analyze external PHP projects when your host system runs an incompatible PHP version (e.g., PHP 7.x or 8.0-8.2).

## Why Use Docker for External Projects?

BC Check requires **PHP 8.3 or higher**, but many production systems or legacy projects still run older PHP versions. Docker allows you to:

- ✅ Run BC Check on any system with Docker installed
- ✅ Analyze projects without modifying their PHP environment
- ✅ Test backward compatibility across different PHP versions
- ✅ Avoid version conflicts between host and project requirements

## Prerequisites

- **Docker** installed on your system
- **Git** access to the target repository
- **Basic command line knowledge**

## Quick Start

### 1. Clone BC Check Repository

```bash
git clone https://github.com/Phauthentic/bc-check.git
cd bc-check
```

### 2. Build the Docker Image

```bash
docker-compose build
```

This creates a Docker image with PHP 8.3 and all necessary dependencies.

### 3. Run BC Check Against an External Project

```bash
# Mount the external project as a volume
docker-compose run --rm -v /path/to/external/project:/target:ro php bin/bc-check check /target v1.0.0 v2.0.0
```

Replace:
- `/path/to/external/project` with the actual path to your target project
- `v1.0.0` and `v2.0.0` with the actual git references you want to compare

## Detailed Usage Examples

### Example 1: Local Project Directory

```bash
# Analyze a project in your home directory
docker-compose run --rm -v ~/projects/my-app:/target:ro php bin/bc-check check /target main develop
```

### Example 2: Remote Repository Analysis

```bash
# Clone and analyze a remote repository
git clone https://github.com/example/project.git /tmp/project
docker-compose run --rm -v /tmp/project:/target:ro php bin/bc-check check /target v1.0.0 v2.0.0
```

### Example 3: CI/CD Pipeline Integration

```bash
# In a CI environment, mount the workspace
docker-compose run --rm -v $(pwd):/target:ro php bin/bc-check check /target $BASE_REF $HEAD_REF
```

## Advanced Configuration

### Custom Configuration File

```bash
# Mount a custom config file
docker-compose run --rm \
  -v /path/to/project:/target:ro \
  -v /path/to/bc-check.yaml:/config/bc-check.yaml:ro \
  php bin/bc-check check /target v1.0.0 v2.0.0 --config=/config/bc-check.yaml
```

### Different Output Formats

```bash
# JSON output for programmatic processing
docker-compose run --rm -v /path/to/project:/target:ro php bin/bc-check check /target v1.0.0 v2.0.0 --format=json

# GitHub Actions format
docker-compose run --rm -v /path/to/project:/target:ro php bin/bc-check check /target v1.0.0 v2.0.0 --format=github-actions
```

### Verbose File Processing

```bash
# Show which files are being processed
docker-compose run --rm -v /path/to/project:/target:ro php bin/bc-check check /target v1.0.0 v2.0.0 --show-files
```

Output example:
```
Processing (source): src/Service/UserService.php
Processing (source): src/Entity/User.php
Processing (target): src/Service/UserService.php
Processing (target): src/Entity/User.php

Found 2 BC break(s):
  ✗ [METHOD_REMOVED] Public method App\Service\UserService::getUser() was removed
  ✗ [METHOD_SIGNATURE_CHANGED] Method App\Service\UserService::createUser() has more required parameters (1 -> 2)

Time: 0.45s
```

## Makefile Integration

For repeated analysis, create a Makefile in the BC Check directory:

```makefile
.PHONY: analyze-external

# Analyze external project
# Usage: make analyze-external PROJECT_PATH=/path/to/project FROM=v1.0.0 TO=v2.0.0
analyze-external:
ifndef PROJECT_PATH
	$(error PROJECT_PATH is required. Usage: make analyze-external PROJECT_PATH=/path/to/project FROM=v1.0.0 TO=v2.0.0)
endif
ifndef FROM
	$(error FROM is required. Usage: make analyze-external PROJECT_PATH=/path/to/project FROM=v1.0.0 TO=v2.0.0)
endif
ifndef TO
	$(error TO is required. Usage: make analyze-external PROJECT_PATH=/path/to/project FROM=v1.0.0 TO=v2.0.0)
endif
	docker-compose run --rm -v $(PROJECT_PATH):/target:ro php bin/bc-check check /target $(FROM) $(TO) $(if $(CONFIG),--config=$(CONFIG),) $(if $(FORMAT),--format=$(FORMAT),) $(if $(filter true,$(VERBOSE)),--show-files,)
```

Usage:
```bash
make analyze-external PROJECT_PATH=/home/user/my-project FROM=v1.0.0 TO=main
make analyze-external PROJECT_PATH=/home/user/my-project FROM=v1.0.0 TO=main FORMAT=json
make analyze-external PROJECT_PATH=/home/user/my-project FROM=v1.0.0 TO=main VERBOSE=true
```

## Troubleshooting

### Permission Issues

If you encounter permission errors:

```bash
# Ensure the target directory is readable
chmod -R a+r /path/to/external/project
```

### Git Repository Issues

If the target isn't a git repository or has issues:

```bash
# Verify the target is a valid git repository
cd /path/to/external/project
git status
git log --oneline -5
```

### Docker Volume Mounting Issues

On some systems, you might need to use absolute paths:

```bash
# Use absolute paths for volume mounting
docker-compose run --rm -v $(realpath /path/to/project):/target:ro php bin/bc-check check /target v1.0.0 v2.0.0
```

### Memory Issues

For large codebases, increase Docker memory:

```bash
# In docker-compose.yml, add memory limits
services:
  php:
    # ... existing config ...
    deploy:
      resources:
        limits:
          memory: 1G
```

## Performance Considerations

- **Volume mounting**: Use `:ro` (read-only) for better performance and security
- **Caching**: Docker layer caching speeds up subsequent runs
- **Resource limits**: Monitor memory usage for large projects

## Integration Examples

### GitHub Actions

```yaml
name: BC Check
on: [pull_request]

jobs:
  bc-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          path: target-project

      - uses: actions/checkout@v4
        with:
          repository: 'Phauthentic/bc-check'
          path: bc-check

      - name: Run BC Check
        working-directory: bc-check
        run: |
          docker-compose build
          docker-compose run --rm -v ../target-project:/target:ro php bin/bc-check check /target ${{ github.event.pull_request.base.sha }} ${{ github.event.pull_request.head.sha }}
```

### Jenkins Pipeline

```groovy
pipeline {
    agent any
    stages {
        stage('BC Check') {
            steps {
                sh '''
                    git clone https://github.com/Phauthentic/bc-check.git bc-check
                    cd bc-check
                    docker-compose build
                    docker-compose run --rm -v ../:/target:ro php bin/bc-check check /target main ${GIT_COMMIT}
                '''
            }
        }
    }
}
```

---

**Note**: This approach ensures BC Check runs in its required PHP 8.3+ environment while analyzing projects on systems with older PHP versions, enabling backward compatibility testing across different PHP ecosystems.