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

namespace Phauthentic\BcCheck\Factory;

use Phauthentic\BcCheck\Config\ConfigurationException;
use Phauthentic\BcCheck\Config\ConfigurationInterface;
use Phauthentic\BcCheck\Detector\BcBreakDetectorInterface;
use Phauthentic\BcCheck\Detector\ClassMadeAbstractDetector;
use Phauthentic\BcCheck\Detector\ClassMadeFinalDetector;
use Phauthentic\BcCheck\Detector\ConstantRemovedDetector;
use Phauthentic\BcCheck\Detector\ConstantVisibilityReducedDetector;
use Phauthentic\BcCheck\Detector\DetectorRegistry;
use Phauthentic\BcCheck\Detector\InterfaceRemovedDetector;
use Phauthentic\BcCheck\Detector\MethodMadeAbstractDetector;
use Phauthentic\BcCheck\Detector\MethodMadeFinalDetector;
use Phauthentic\BcCheck\Detector\MethodMadeStaticDetector;
use Phauthentic\BcCheck\Detector\MethodRemovedDetector;
use Phauthentic\BcCheck\Detector\MethodReturnTypeChangedDetector;
use Phauthentic\BcCheck\Detector\MethodSignatureChangedDetector;
use Phauthentic\BcCheck\Detector\MethodVisibilityReducedDetector;
use Phauthentic\BcCheck\Detector\ParentChangedDetector;
use Phauthentic\BcCheck\Detector\PropertyMadeReadonlyDetector;
use Phauthentic\BcCheck\Detector\PropertyMadeStaticDetector;
use Phauthentic\BcCheck\Detector\PropertyRemovedDetector;
use Phauthentic\BcCheck\Detector\PropertyTypeChangedDetector;
use Phauthentic\BcCheck\Detector\PropertyVisibilityReducedDetector;

final readonly class DetectorRegistryFactory
{
    /**
     * Create a detector registry with built-in detectors.
     */
    public function create(): DetectorRegistry
    {
        return new DetectorRegistry($this->getBuiltInDetectors());
    }

    /**
     * Create a detector registry with built-in and external detectors from configuration.
     */
    public function createWithConfiguration(ConfigurationInterface $config): DetectorRegistry
    {
        $detectors = $this->getBuiltInDetectors();

        foreach ($config->getExternalDetectors() as $class) {
            if (!class_exists($class)) {
                throw ConfigurationException::externalDetectorNotFound($class);
            }

            $detector = new $class();

            if (!$detector instanceof BcBreakDetectorInterface) {
                throw ConfigurationException::externalDetectorInvalidInterface(
                    $class,
                    BcBreakDetectorInterface::class,
                );
            }

            $detectors[] = $detector;
        }

        return new DetectorRegistry($detectors);
    }

    /**
     * @return list<BcBreakDetectorInterface>
     */
    private function getBuiltInDetectors(): array
    {
        return [
            // Class-level detectors
            new ClassMadeFinalDetector(),
            new ClassMadeAbstractDetector(),
            new InterfaceRemovedDetector(),
            new ParentChangedDetector(),

            // Method-level detectors
            new MethodRemovedDetector(),
            new MethodSignatureChangedDetector(),
            new MethodReturnTypeChangedDetector(),
            new MethodVisibilityReducedDetector(),
            new MethodMadeFinalDetector(),
            new MethodMadeStaticDetector(),
            new MethodMadeAbstractDetector(),

            // Property-level detectors
            new PropertyRemovedDetector(),
            new PropertyVisibilityReducedDetector(),
            new PropertyTypeChangedDetector(),
            new PropertyMadeReadonlyDetector(),
            new PropertyMadeStaticDetector(),

            // Constant-level detectors
            new ConstantRemovedDetector(),
            new ConstantVisibilityReducedDetector(),
        ];
    }
}
