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

namespace Phauthentic\BcCheck\Tests\Unit\Factory;

use Phauthentic\BcCheck\Config\Configuration;
use Phauthentic\BcCheck\Config\ConfigurationException;
use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\Factory\DetectorRegistryFactory;
use Phauthentic\BcCheck\ValueObject\ClassInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

// Test detector for external loading test
class TestExternalDetector implements BcBreakDetectorInterface
{
    public function detect(ClassInfo $before, ClassInfo $after): array
    {
        return [];
    }
}

// Invalid detector that doesn't implement the interface
class InvalidDetector
{
}

#[CoversClass(DetectorRegistryFactory::class)]
final class DetectorRegistryFactoryTest extends TestCase
{
    private DetectorRegistryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DetectorRegistryFactory();
    }

    public function testCreateReturnsRegistryWithBuiltInDetectors(): void
    {
        $registry = $this->factory->create();

        // Should have 18 built-in detectors
        $this->assertCount(18, $registry->getDetectors());
    }

    public function testCreateWithConfigurationIncludesExternalDetectors(): void
    {
        $config = new Configuration(
            externalDetectors: [TestExternalDetector::class],
        );

        $registry = $this->factory->createWithConfiguration($config);

        // 18 built-in + 1 external
        $this->assertCount(19, $registry->getDetectors());
    }

    public function testCreateWithConfigurationNoExternalDetectors(): void
    {
        $config = new Configuration(externalDetectors: []);

        $registry = $this->factory->createWithConfiguration($config);

        $this->assertCount(18, $registry->getDetectors());
    }

    public function testCreateWithConfigurationThrowsOnMissingClass(): void
    {
        $config = new Configuration(
            externalDetectors: ['NonExistent\\Detector\\Class'], // @phpstan-ignore argument.type
        );

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('not found');

        $this->factory->createWithConfiguration($config);
    }

    public function testCreateWithConfigurationThrowsOnInvalidDetector(): void
    {
        $config = new Configuration(
            externalDetectors: [InvalidDetector::class],
        );

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('must implement');

        $this->factory->createWithConfiguration($config);
    }
}
