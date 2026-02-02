# Installation

BC Check can be installed in several ways depending on your needs.

## Requirements

- PHP 8.4 or higher
- Git (for analyzing repositories)
- Composer (for installation)

## Composer (Recommended)

Install BC Check as a development dependency in your project:

```bash
composer require --dev phauthentic/bc-check
```

After installation, the `bc-check` command will be available at:

```bash
vendor/bin/bc-check
```

## Global Installation

Install BC Check globally for use across all projects:

```bash
composer global require phauthentic/bc-check
```

Make sure your Composer global bin directory is in your PATH:

```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

## PHAR Download

Download the standalone PHAR file from the [releases page](https://github.com/Phauthentic/bc-check/releases):

```bash
# Download latest release
curl -LSs https://github.com/Phauthentic/bc-check/releases/latest/download/bc-check.phar -o bc-check.phar

# Make it executable
chmod +x bc-check.phar

# Optionally move to a directory in your PATH
sudo mv bc-check.phar /usr/local/bin/bc-check
```

## Docker

Build and run using Docker:

```bash
# Build the Docker image
docker-compose build

# Run BC Check
docker-compose run --rm php bin/bc-check check /app abc123 def456
```

Or use the Docker image directly:

```bash
docker run --rm -v $(pwd):/app phauthentic/bc-check check /app abc123 def456
```

## Verify Installation

Verify the installation by running:

```bash
bc-check --version
```

Or with Composer:

```bash
vendor/bin/bc-check --version
```

## Next Steps

- [Usage Guide](Usage.md) - Learn how to use BC Check
- [Configuration](Configuration.md) - Configure BC Check for your project
