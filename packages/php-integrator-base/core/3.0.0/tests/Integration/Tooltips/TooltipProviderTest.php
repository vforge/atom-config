<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use PhpIntegrator\Tooltips\TooltipResult;

class TooltipProviderTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testFuncCallContainsAllSections(): void
    {
        $this->performTooltipTest('FuncCallAllSections.phpt', 435, 440, 'Hi! *Bold text* **Italic** ~~Strikethrough~~

# Description
## Header
Hello!

# Parameters
   |   |   ' . '
--- | --- | ---
**•&nbsp;$first** | *string* | Testdescription
**•&nbsp;$second** | *int* | Test
**•&nbsp;$third** | *\Exception* | Test

# Returns
*string&#124;bool*

# Throws
   |   |   ' . '
--- | --- | ---
•&nbsp;**\Exception** | When something happens
•&nbsp;**\LogicException** | When something else happens.
•&nbsp;**\RuntimeException** |  ');
    }

    /**
     * @return void
     */
    public function testUnqualifiedFunctionCall(): void
    {
        $this->performTooltipTest('UnqualifiedFuncCall.phpt', 57, 62, "This is a summary.\n\n# Returns\n*void*");
    }

    /**
     * @return void
     */
    public function testQualifiedFunctionCall(): void
    {
        $this->performTooltipTest('QualifiedFuncCall.phpt', 87, 94, "This is a summary.\n\n# Returns\n*void*");
    }

    /**
     * @return void
     */
    public function testFullyQualifiedFunctionCall(): void
    {
        $this->performTooltipTest('FullyQualifiedFuncCall.phpt', 87, 97, "This is a summary.\n\n# Returns\n*void*");
    }

    /**
     * @return void
     */
    public function testConstFetchContainsAllSections(): void
    {
        $this->performTooltipTest('ConstFetchAllSections.phpt', 134, 137, 'Hi! *Bold text* **Italic** ~~Strikethrough~~

# Description
## Header
Hello!

# Type
*string&#124;bool*');
    }

    /**
     * @return void
     */
    public function testUnqualifiedConstFetch(): void
    {
        $this->performTooltipTest('UnqualifiedConstFetch.phpt', 59, 62, "This is a summary.\n\n# Type\n*int*");
    }

    /**
     * @return void
     */
    public function testQualifiedConstFetch(): void
    {
        $this->performTooltipTest('QualifiedConstFetch.phpt', 89, 94, "This is a summary.\n\n# Type\n*int*");
    }

    /**
     * @return void
     */
    public function testFullyQualifiedConstFetch(): void
    {
        $this->performTooltipTest('FullyQualifiedConstFetch.phpt', 89, 97, "This is a summary.\n\n# Type\n*int*");
    }

    /**
     * @return void
     */
    public function testClassConstFetch(): void
    {
        $this->performTooltipTest('ClassConstFetch.phpt', 108, 115, "This is a summary.\n\n# Type\n*int*");
    }

    /**
     * @return void
     */
    public function testClassNameContainsAllSections(): void
    {
        $this->performTooltipTest('ClassNameAllSections.phpt', 316, 326, 'Hi! *Bold text* **Italic** ~~Strikethrough~~

# Description
## Header
Hello!

# Full Name
*\A\SimpleClass*

# Type
Class');

        $this->performTooltipTest('ClassNameAllSections.phpt', 340, 352, '(No documentation available)

# Full Name
*\A\AbstractClass*

# Type
Abstract class');

        $this->performTooltipTest('ClassNameAllSections.phpt', 366, 376, '(No documentation available)

# Full Name
*\A\SimpleTrait*

# Type
Trait');

        $this->performTooltipTest('ClassNameAllSections.phpt', 390, 404, '(No documentation available)

# Full Name
*\A\SimpleInterface*

# Type
Interface');
    }

    /**
     * @return void
     */
    public function testClassNameInClassConstFetch(): void
    {
        $this->performTooltipTest('ClassNameClassConstFetch.phpt', 93, 93, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInStaticMethodCall(): void
    {
        $this->performTooltipTest('ClassNameStaticMethodCall.phpt', 106, 106, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInStaticPropertyFetch(): void
    {
        $this->performTooltipTest('ClassNameStaticPropertyFetch.phpt', 86, 86, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInUseImport(): void
    {
        $this->performTooltipTest('ClassNameUseImport.phpt', 83, 85, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInImplementsClause(): void
    {
        $this->performTooltipTest('ClassNameImplements.phpt', 82, 82, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInExtendsClause(): void
    {
        $this->performTooltipTest('ClassNameExtends.phpt', 79, 79, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testClassNameInTraitUse(): void
    {
        $this->performTooltipTest('ClassNameTraitUse.phpt', 81, 81, 'This is a summary.

# Full Name
*\A\B*

# Type
Trait');
    }

    /**
     * @return void
     */
    public function testClassNameInTraitAlias(): void
    {
        $this->performTooltipTest('ClassNameTraitAlias.phpt', 123, 123, 'This is a summary.

# Full Name
*\A\B*

# Type
Trait');
    }

    /**
     * @return void
     */
    public function testClassNameInTraitPrecedence(): void
    {
        $this->performTooltipTest('ClassNameTraitPrecedence.phpt', 165, 165, 'This is a summary.

# Full Name
*\A\B*

# Type
Trait');
    }

    /**
     * @return void
     */
    public function testClassNameAfterNewKeyword(): void
    {
        $this->performTooltipTest('ClassNameNewKeyword.phpt', 72, 72, 'This is a summary.

# Full Name
*\A\B*

# Type
Class');
    }

    /**
     * @return void
     */
    public function testFunctionDefinition(): void
    {
        $this->performTooltipTest('FunctionDefinition.phpt', 46, 49, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @return void
     */
    public function testClassMethodDefinition(): void
    {
        $this->performTooltipTest('ClassMethodDefinition.phpt', 79, 82, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @return void
     */
    public function testMethodCall(): void
    {
        $this->performTooltipTest('MethodCall.phpt', 113, 116, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @return void
     */
    public function testStaticMethodCall(): void
    {
        $this->performTooltipTest('StaticMethodCall.phpt', 105, 108, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @return void
     */
    public function testPropertyFetchContainsAllSections(): void
    {
        $this->performTooltipTest('PropertyFetchAllSections.phpt', 193, 196, 'Hi! *Bold text* **Italic** ~~Strikethrough~~

# Description
## Header
Hello!

# Type
*string&#124;bool*');
    }

    /**
     * @return void
     */
    public function testStaticPropertyFetch(): void
    {
        $this->performTooltipTest('StaticPropertyFetch.phpt', 90, 94, 'This is a summary.

# Type
(Not known)');
    }

    /**
     * @return void
     */
    public function testFunctionDefinitionRangeIsConfinedToBeforeFirstStatement(): void
    {
        $this->performTooltipTest('FunctionDefinitionWithStatement.phpt', 46, 49, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @return void
     */
    public function testFunctionDefinitionRangeIsConfinedToBeforeFirstParameter(): void
    {
        $fileName = 'FunctionDefinitionWithParameter.phpt';

        $this->performTooltipTest($fileName, 46, 49, 'This is a summary.

# Parameters
   |   |   ' . '
--- | --- | ---
**•&nbsp;$param1** | *int* |  ' . '
**•&nbsp;$param2** | *bool* |  ' . '

# Returns
*void*');

        $this->assertNull($this->getTooltip(
            $this->container->get('storage')->getFileByPath($this->getPathFor($fileName)),
            69
        ));
    }

    /**
     * @return void
     */
    public function testFunctionDefinitionRangeIsConfinedToNonScalarReturnType(): void
    {
        $this->performTooltipTest('FunctionDefinitionWithReturnType.phpt', 46, 49, 'This is a summary.

# Returns
*void*');
    }

    /**
     * @param string $fileName
     * @param int    $start
     * @param int    $end
     * @param string $contents
     *
     * @return void
     */
    protected function performTooltipTest(string $fileName, int $start, int $end, string $contents): void
    {
        $path = $this->getPathFor($fileName);

        $this->indexTestFile($this->container, $path);

        $file = $this->container->get('storage')->getFileByPath($path);

        $i = $start;

        while ($i <= $end) {
            $result = $this->getTooltip($file, $i);

            $this->assertNotNull($result, "No tooltip was returned for location {$i} in {$fileName}");
            $this->assertNull($result->getRange());
            $this->assertEquals($contents, $result->getContents());

            ++$i;
        }

        // Assert that the range doesn't extend longer than it should.
        $resultBeforeRange = $this->getTooltip($file, $start - 1);
        $this->assertTrue(
            $resultBeforeRange === null || $resultBeforeRange->getContents() !== $contents,
            "Range does not start exactly at position {$start}, but seems to continue before it"
        );

        $resultAfterRange = $this->getTooltip($file, $end + 1);
        $this->assertTrue(
            $resultAfterRange === null || $resultAfterRange->getContents() !== $contents,
            "Range does not end exactly at position {$end}, but seems to continue after it"
        );
    }

    /**
     * @param Structures\File $file
     * @param int             $position
     *
     * @return TooltipResult|null
     */
    protected function getTooltip(Structures\File $file, int $position): ?TooltipResult
    {
        $code = $this->container->get('sourceCodeStreamReader')->getSourceCodeFromFile($file->getPath());

        return $this->container->get('tooltipProvider')->get($file, $code, $position);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/TooltipProviderTest/' . $file;
    }
}
