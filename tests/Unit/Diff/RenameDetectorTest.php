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

namespace Phauthentic\BcCheck\Tests\Unit\Diff;

use Phauthentic\BcCheck\Diff\RenameDetector;
use Phauthentic\BcCheck\Diff\RenameMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RenameDetector::class)]
#[CoversClass(RenameMap::class)]
final class RenameDetectorTest extends TestCase
{
    private RenameDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new RenameDetector();
    }

    public function testDetectsMethodRename(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    public function oldMethodName(): void
+    public function newMethodName(): void
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertArrayHasKey('src/Service.php', $result);
        $renameMap = $result['src/Service.php'];
        $this->assertInstanceOf(RenameMap::class, $renameMap);
        $this->assertSame('newMethodName', $renameMap->getMethodNewName('oldMethodName'));
    }

    public function testDetectsPropertyRename(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Entity.php b/src/Entity.php
index abc123..def456 100644
--- a/src/Entity.php
+++ b/src/Entity.php
@@ -5,5 +5,5 @@ class Entity
-    public string $oldProperty;
+    public string $newProperty;
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertArrayHasKey('src/Entity.php', $result);
        $renameMap = $result['src/Entity.php'];
        $this->assertSame('newProperty', $renameMap->getPropertyNewName('oldProperty'));
    }

    public function testDetectsMultipleRenamesInSameFile(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,7 +10,7 @@ class Service
-    public function methodOne(): void
+    public function renamedMethodOne(): void
     {
     }
 
-    protected function methodTwo(): void
+    protected function renamedMethodTwo(): void
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertArrayHasKey('src/Service.php', $result);
        $renameMap = $result['src/Service.php'];
        $this->assertSame('renamedMethodOne', $renameMap->getMethodNewName('methodOne'));
        $this->assertSame('renamedMethodTwo', $renameMap->getMethodNewName('methodTwo'));
    }

    public function testReturnsNullForNonExistentRename(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    public function oldMethod(): void
+    public function newMethod(): void
DIFF;

        $result = $this->detector->detect($diff);
        $renameMap = $result['src/Service.php'];

        $this->assertNull($renameMap->getMethodNewName('nonExistent'));
    }

    public function testReturnsEmptyArrayForEmptyDiff(): void
    {
        $result = $this->detector->detect('');

        $this->assertSame([], $result);
    }

    public function testReturnsEmptyArrayForDiffWithNoRenames(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    // Old comment
+    // New comment
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertSame([], $result);
    }

    public function testHandlesMethodWithModifiers(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    final public static function oldStaticMethod(): void
+    final public static function newStaticMethod(): void
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertArrayHasKey('src/Service.php', $result);
        $renameMap = $result['src/Service.php'];
        $this->assertSame('newStaticMethod', $renameMap->getMethodNewName('oldStaticMethod'));
    }

    public function testHandlesProtectedMethod(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    protected function oldProtected(): void
+    protected function newProtected(): void
DIFF;

        $result = $this->detector->detect($diff);

        $renameMap = $result['src/Service.php'];
        $this->assertSame('newProtected', $renameMap->getMethodNewName('oldProtected'));
    }

    public function testDoesNotMatchDifferentVisibilities(): void
    {
        // When visibility changes, it's not just a rename - it's a visibility change too
        // So we don't detect it as a rename (the visibility detector will catch it)
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    public function methodName(): void
+    protected function methodName(): void
DIFF;

        $result = $this->detector->detect($diff);

        // No rename detected because it's the same method with visibility change
        $this->assertSame([], $result);
    }

    public function testHandlesPropertyWithType(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Entity.php b/src/Entity.php
index abc123..def456 100644
--- a/src/Entity.php
+++ b/src/Entity.php
@@ -5,5 +5,5 @@ class Entity
-    protected ?int $oldCounter = null;
+    protected ?int $newCounter = null;
DIFF;

        $result = $this->detector->detect($diff);

        $renameMap = $result['src/Entity.php'];
        $this->assertSame('newCounter', $renameMap->getPropertyNewName('oldCounter'));
    }

    public function testHandlesMultipleFilesInDiff(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/ServiceA.php b/src/ServiceA.php
index abc123..def456 100644
--- a/src/ServiceA.php
+++ b/src/ServiceA.php
@@ -10,5 +10,5 @@ class ServiceA
-    public function oldA(): void
+    public function newA(): void
diff --git a/src/ServiceB.php b/src/ServiceB.php
index abc123..def456 100644
--- a/src/ServiceB.php
+++ b/src/ServiceB.php
@@ -10,5 +10,5 @@ class ServiceB
-    public function oldB(): void
+    public function newB(): void
DIFF;

        $result = $this->detector->detect($diff);

        $this->assertArrayHasKey('src/ServiceA.php', $result);
        $this->assertArrayHasKey('src/ServiceB.php', $result);
        $this->assertSame('newA', $result['src/ServiceA.php']->getMethodNewName('oldA'));
        $this->assertSame('newB', $result['src/ServiceB.php']->getMethodNewName('oldB'));
    }

    public function testRenameMapHasMethodRenames(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Service.php b/src/Service.php
index abc123..def456 100644
--- a/src/Service.php
+++ b/src/Service.php
@@ -10,5 +10,5 @@ class Service
-    public function oldMethod(): void
+    public function newMethod(): void
DIFF;

        $result = $this->detector->detect($diff);
        $renameMap = $result['src/Service.php'];

        $this->assertTrue($renameMap->hasMethodRenames());
        $this->assertFalse($renameMap->hasPropertyRenames());
    }

    public function testRenameMapHasPropertyRenames(): void
    {
        $diff = <<<'DIFF'
diff --git a/src/Entity.php b/src/Entity.php
index abc123..def456 100644
--- a/src/Entity.php
+++ b/src/Entity.php
@@ -5,5 +5,5 @@ class Entity
-    public $oldProp;
+    public $newProp;
DIFF;

        $result = $this->detector->detect($diff);
        $renameMap = $result['src/Entity.php'];

        $this->assertFalse($renameMap->hasMethodRenames());
        $this->assertTrue($renameMap->hasPropertyRenames());
    }
}
