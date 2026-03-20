<?php

declare(strict_types=1);

/**
 * Example: Registry retrieval and breadcrumb rendering in osCommerce 2.x.
 *
 * This example shows the two most common patterns in osCommerce2:
 *   1. Retrieving shared services via OSC\OM\Registry.
 *   2. Building and rendering a breadcrumb trail.
 *
 * In a real installation, the Registry is populated during application_top.php
 * and the breadcrumb is built by the page controller before the template renders.
 */

use OSC\OM\Registry;

// --- 1. Registry usage ---

// Retrieve the database object (registered during bootstrap)
/** @var \OSC\OM\DB $db */
$db = Registry::get('Db');

// Safe retrieval pattern — avoids triggering a notice on miss
if (Registry::exists('Language')) {
    $language = Registry::get('Language');
}

// --- 2. Breadcrumb trail ---

require_once __DIR__ . '/../catalog/includes/classes/breadcrumb.php';

$breadcrumb = new breadcrumb();

// Build a typical category > product trail
$breadcrumb->add('Home', 'https://example.com/');
$breadcrumb->add('Books', 'https://example.com/books');
$breadcrumb->add('The Great Gatsby'); // last item — no link

// Render as Schema.org BreadcrumbList HTML
echo $breadcrumb->trail();
// Outputs: <ol itemscope itemtype="http://schema.org/BreadcrumbList" class="breadcrumb">
//            <li ...><a href="...">Home</a>...</li>
//            <li ...><a href="...">Books</a>...</li>
//            <li ...><span itemprop="name">The Great Gatsby</span>...</li>
//          </ol>
