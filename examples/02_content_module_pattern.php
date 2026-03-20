<?php

declare(strict_types=1);

/**
 * Example: How a content module is structured in osCommerce2.
 *
 * Content modules live under catalog/includes/modules/content/{group}/ and
 * implement a standard interface (execute, is_enabled, check, install, remove,
 * keys). The storefront loads all enabled modules for a given group and calls
 * execute() on each.
 *
 * This example shows a minimal standalone module with the explicit variable
 * assignments that replace the extract($GLOBALS, EXTR_SKIP) anti-pattern.
 */

use OSC\OM\Registry;

class cm_example_module
{
    public string $code;
    public string $group;
    public bool $enabled = false;

    public function __construct()
    {
        $this->code    = static::class;
        $this->group   = 'index';
        $this->enabled = defined('MODULE_CONTENT_EXAMPLE_STATUS')
                      && MODULE_CONTENT_EXAMPLE_STATUS === 'True';
    }

    /**
     * Execute the module — render its output into the template.
     *
     * Notice: explicit $var = $GLOBALS['key'] assignments are used instead of
     * extract($GLOBALS, EXTR_SKIP) to make dependencies explicit and auditable.
     */
    public function execute(): void
    {
        global $osc_template;

        // Explicit global access — replace extract($GLOBALS, EXTR_SKIP)
        $db           = $GLOBALS['db']           ?? null;
        $languages_id = $GLOBALS['languages_id'] ?? null;
        $currency     = $GLOBALS['currency']     ?? null;

        ob_start();
        echo '<p>Example module output. Language: ' . (int) $languages_id . '</p>';
        $html = ob_get_clean();

        $osc_template->add_content($html, $this->group);
    }

    public function is_enabled(): bool
    {
        return $this->enabled;
    }

    public function check(): bool
    {
        return defined('MODULE_CONTENT_EXAMPLE_STATUS');
    }

    public function install(): void
    {
        $db = Registry::get('Db');
        $db->save('configuration', [
            'configuration_key'   => 'MODULE_CONTENT_EXAMPLE_STATUS',
            'configuration_value' => 'True',
            'configuration_group_id' => '6',
            'date_added'          => 'now()',
        ]);
    }

    public function remove(): mixed
    {
        return Registry::get('Db')->exec(
            'DELETE FROM :table_configuration WHERE configuration_key IN ("'
            . implode('", "', $this->keys()) . '")'
        );
    }

    public function keys(): array
    {
        return ['MODULE_CONTENT_EXAMPLE_STATUS'];
    }
}
