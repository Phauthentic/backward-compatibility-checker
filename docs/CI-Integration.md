# CI/CD Integration

BC Check can be integrated into your CI/CD pipeline to automatically detect backward compatibility breaks.

## GitHub Actions

### Basic Workflow

```yaml
name: BC Check

on:
  pull_request:
    branches: [main]

jobs:
  bc-check:
    name: Backward Compatibility Check
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0  # Required to access git history

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: none

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run BC Check
        run: |
          # Get the base branch
          BASE_SHA=$(git merge-base origin/${{ github.base_ref }} HEAD)
          vendor/bin/bc-check check . $BASE_SHA HEAD --format=github-actions
```

### With Release Tags

```yaml
name: BC Check on Release

on:
  push:
    tags:
      - 'v*.*.*'

jobs:
  bc-check:
    name: Check BC against previous release
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Get previous tag
        id: prev_tag
        run: |
          PREV_TAG=$(git describe --tags --abbrev=0 HEAD^ 2>/dev/null || echo "")
          echo "tag=$PREV_TAG" >> $GITHUB_OUTPUT

      - name: Run BC Check
        if: steps.prev_tag.outputs.tag != ''
        run: |
          vendor/bin/bc-check check . ${{ steps.prev_tag.outputs.tag }} HEAD --format=github-actions
```

## GitLab CI

```yaml
bc-check:
  stage: test
  image: php:8.4-cli
  before_script:
    - apt-get update && apt-get install -y git unzip
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --prefer-dist --no-progress
  script:
    - |
      if [ -n "$CI_MERGE_REQUEST_TARGET_BRANCH_NAME" ]; then
        BASE_SHA=$(git merge-base origin/$CI_MERGE_REQUEST_TARGET_BRANCH_NAME HEAD)
        vendor/bin/bc-check check . $BASE_SHA HEAD
      fi
  rules:
    - if: $CI_PIPELINE_SOURCE == "merge_request_event"
```

## CircleCI

```yaml
version: 2.1

jobs:
  bc-check:
    docker:
      - image: php:8.4-cli
    steps:
      - checkout
      - run:
          name: Install dependencies
          command: |
            apt-get update && apt-get install -y git unzip
            curl -sS https://getcomposer.org/installer | php
            php composer.phar install --prefer-dist --no-progress
      - run:
          name: Run BC Check
          command: |
            if [ -n "$CIRCLE_PULL_REQUEST" ]; then
              BASE_SHA=$(git merge-base origin/main HEAD)
              vendor/bin/bc-check check . $BASE_SHA HEAD
            fi

workflows:
  version: 2
  test:
    jobs:
      - bc-check
```

## Jenkins

```groovy
pipeline {
    agent {
        docker {
            image 'php:8.4-cli'
        }
    }

    stages {
        stage('Install') {
            steps {
                sh 'apt-get update && apt-get install -y git unzip'
                sh 'curl -sS https://getcomposer.org/installer | php'
                sh 'php composer.phar install --prefer-dist --no-progress'
            }
        }

        stage('BC Check') {
            when {
                changeRequest()
            }
            steps {
                sh '''
                    BASE_SHA=$(git merge-base origin/${CHANGE_TARGET} HEAD)
                    vendor/bin/bc-check check . $BASE_SHA HEAD
                '''
            }
        }
    }
}
```

## Bitbucket Pipelines

```yaml
image: php:8.4-cli

pipelines:
  pull-requests:
    '**':
      - step:
          name: BC Check
          script:
            - apt-get update && apt-get install -y git unzip
            - curl -sS https://getcomposer.org/installer | php
            - php composer.phar install --prefer-dist --no-progress
            - |
              BASE_SHA=$(git merge-base origin/$BITBUCKET_PR_DESTINATION_BRANCH HEAD)
              vendor/bin/bc-check check . $BASE_SHA HEAD
```

## Docker-based CI

Use the provided Docker image:

```yaml
# GitHub Actions example
- name: Run BC Check
  run: |
    docker run --rm -v $(pwd):/app \
      -w /app \
      phauthentic/bc-check \
      check . $BASE_SHA HEAD --format=github-actions
```

## Exit Codes

BC Check returns different exit codes for CI integration:

| Exit Code | Meaning |
|-----------|---------|
| 0 | No BC breaks found |
| 1 | BC breaks detected or error occurred |

Use these exit codes to fail your build when BC breaks are detected.

## JSON Output for Custom Processing

Use JSON output for custom processing in CI:

```bash
# Get BC breaks as JSON
bc-check check . v1.0.0 v2.0.0 --format=json > bc-breaks.json

# Example: Count breaks
BREAK_COUNT=$(jq '.total' bc-breaks.json)
if [ "$BREAK_COUNT" -gt 0 ]; then
  echo "Found $BREAK_COUNT BC breaks!"
  jq '.breaks[].message' bc-breaks.json
  exit 1
fi
```

## Allowing Specific Breaks

Use configuration to exclude expected changes:

```yaml
# bc-check.yaml
exclude:
  - '^App\\Internal\\.*'  # Exclude internal classes
  - '.*Test$'             # Exclude test classes
```

## Next Steps

- [Usage](Usage.md) - Learn more about CLI options
- [Configuration](Configuration.md) - Configure filtering
