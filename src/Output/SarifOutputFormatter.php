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

namespace Phauthentic\BcCheck\Output;

use Phauthentic\BcCheck\ValueObject\BcBreak;
use Phauthentic\BcCheck\ValueObject\BcBreakType;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Formats BC breaks as SARIF 2.1.0 (Static Analysis Results Interchange Format).
 *
 * @see https://docs.oasis-open.org/sarif/sarif/v2.1.0/sarif-v2.1.0.html
 */
final readonly class SarifOutputFormatter implements OutputFormatterInterface
{
    private const TOOL_NAME = 'php-bc-check';
    private const TOOL_VERSION = '1.0.0';
    private const SCHEMA_URI = 'https://json.schemastore.org/sarif-2.1.0.json';
    private const SARIF_VERSION = '2.1.0';

    public function format(array $breaks, OutputInterface $output): void
    {
        $rules = $this->buildRules($breaks);
        $results = $this->buildResults($breaks);

        $sarif = [
            '$schema' => self::SCHEMA_URI,
            'version' => self::SARIF_VERSION,
            'runs' => [
                [
                    'tool' => [
                        'driver' => [
                            'name' => self::TOOL_NAME,
                            'version' => self::TOOL_VERSION,
                            'informationUri' => 'https://github.com/phauthentic/bc-check',
                            'rules' => array_values($rules),
                        ],
                    ],
                    'results' => $results,
                ],
            ],
        ];

        $json = json_encode($sarif, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $output->writeln($json);
    }

    /**
     * Build unique rules from the BC breaks.
     *
     * @param list<BcBreak> $breaks
     * @return array<string, array{id: string, shortDescription: array{text: string}}>
     */
    private function buildRules(array $breaks): array
    {
        $rules = [];

        foreach ($breaks as $break) {
            $ruleId = $break->type->value;
            if (!isset($rules[$ruleId])) {
                $rules[$ruleId] = [
                    'id' => $ruleId,
                    'shortDescription' => [
                        'text' => $this->getRuleDescription($break->type),
                    ],
                ];
            }
        }

        return $rules;
    }

    /**
     * Build SARIF results from BC breaks.
     *
     * @param list<BcBreak> $breaks
     * @return list<array{ruleId: string, level: string, message: array{text: string}}>
     */
    private function buildResults(array $breaks): array
    {
        return array_map(
            static fn (BcBreak $break): array => [
                'ruleId' => $break->type->value,
                'level' => 'error',
                'message' => [
                    'text' => $break->message,
                ],
            ],
            $breaks,
        );
    }

    private function getRuleDescription(BcBreakType $type): string
    {
        return match ($type) {
            BcBreakType::ClassRemoved => 'A class was removed from the public API',
            BcBreakType::ClassMadeFinal => 'A class was made final, preventing extension',
            BcBreakType::ClassMadeAbstract => 'A class was made abstract, preventing direct instantiation',
            BcBreakType::InterfaceRemoved => 'An interface was removed from a class',
            BcBreakType::ParentChanged => 'The parent class was changed',
            BcBreakType::MethodRemoved => 'A method was removed from the public API',
            BcBreakType::MethodRenamed => 'A method was renamed',
            BcBreakType::MethodSignatureChanged => 'A method signature was changed',
            BcBreakType::MethodReturnTypeChanged => 'A method return type was changed',
            BcBreakType::MethodVisibilityReduced => 'A method visibility was reduced',
            BcBreakType::MethodMadeFinal => 'A method was made final, preventing override',
            BcBreakType::MethodMadeStatic => 'A method was made static',
            BcBreakType::MethodMadeNonStatic => 'A method was made non-static',
            BcBreakType::MethodMadeAbstract => 'A method was made abstract',
            BcBreakType::PropertyRemoved => 'A property was removed from the public API',
            BcBreakType::PropertyRenamed => 'A property was renamed',
            BcBreakType::PropertyVisibilityReduced => 'A property visibility was reduced',
            BcBreakType::PropertyTypeChanged => 'A property type was changed',
            BcBreakType::PropertyMadeReadonly => 'A property was made readonly',
            BcBreakType::PropertyMadeStatic => 'A property was made static',
            BcBreakType::PropertyMadeNonStatic => 'A property was made non-static',
            BcBreakType::ConstantRemoved => 'A constant was removed from the public API',
            BcBreakType::ConstantVisibilityReduced => 'A constant visibility was reduced',
            BcBreakType::Other => 'A backward compatibility break was detected',
        };
    }
}
