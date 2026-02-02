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

namespace Phauthentic\BcCheck\Parser;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final readonly class FileParser implements FileParserInterface
{
    private Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->createForNewestSupportedVersion();
    }

    public function parse(string $code): array
    {
        $statements = $this->parser->parse($code);

        if ($statements === null) {
            return [];
        }

        $analyzer = new ClassAnalyzer();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($analyzer);
        $traverser->traverse($statements);

        return $analyzer->getClasses();
    }
}
