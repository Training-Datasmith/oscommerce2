<?php

declare(strict_types=1);

/**
 * Tests that cm_cs_downloads in osCommerce2 no longer uses extract($GLOBALS, EXTR_SKIP).
 *
 * The original code used extract($GLOBALS, EXTR_SKIP) before including a template file,
 * which exposes all global state (DB handles, session data, etc.) as local variables in
 * an uncontrolled way. The fix uses explicit variable assignments.
 *
 * These tests verify the fix is in place without requiring a database or web server.
 */

use PHPUnit\Framework\TestCase;

class ExtractGlobalsTest extends TestCase
{
    private string $moduleFile;

    protected function setUp(): void
    {
        $this->moduleFile = dirname(__DIR__, 2)
            . '/catalog/includes/modules/content/checkout_success/cm_cs_downloads.php';
    }

    public function testModuleFileExists(): void
    {
        $this->assertFileExists($this->moduleFile);
    }

    /**
     * Security regression: the module must not call extract($GLOBALS, ...) .
     *
     * Prevented vulnerability: extract($GLOBALS, EXTR_SKIP) in a template context
     * exposes all global variables — including DB credentials, session tokens, and
     * any user-controlled values that were registered as globals — to the included
     * template file, potentially leaking sensitive information or enabling variable
     * injection attacks if combined with user-supplied data.
     */
    public function testNoExtractGlobalsCallInDownloadsModule(): void
    {
        $source = file_get_contents($this->moduleFile);
        $this->assertNotFalse($source);

        $this->assertStringNotContainsString(
            'extract($GLOBALS',
            $source,
            'cm_cs_downloads must not use extract($GLOBALS). Use explicit $var = $GLOBALS["key"] assignments.'
        );
    }

    /**
     * The replacement must explicitly fetch the database handle.
     */
    public function testExplicitDbAssignmentPresent(): void
    {
        $source = file_get_contents($this->moduleFile);
        $this->assertNotFalse($source);

        $this->assertMatchesRegularExpression(
            '/\\\$db\s*=\s*\\\$GLOBALS\[.db.\]/',
            $source,
            'The fix must explicitly assign $db from $GLOBALS.'
        );
    }

    /**
     * The replacement must explicitly fetch languages_id.
     */
    public function testExplicitLanguagesIdAssignmentPresent(): void
    {
        $source = file_get_contents($this->moduleFile);
        $this->assertNotFalse($source);

        $this->assertMatchesRegularExpression(
            '/\\\$languages_id\s*=\s*\\\$GLOBALS\[.languages_id.\]/',
            $source,
            'The fix must explicitly assign $languages_id from $GLOBALS.'
        );
    }
}
