<?php

declare(strict_types=1);
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

/**
 * Builds and renders a Schema.org-annotated breadcrumb trail.
 *
 * Entries are added in order (optionally prepended) and rendered as an
 * ordered list with itemscope/itemprop attributes suitable for Google rich results.
 *
 * @since  2016
 */
class breadcrumb
{
    /** @var array<int, array{title: string, link: string}>  Ordered breadcrumb entries. */
    public $_trail;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears all breadcrumb entries.
     *
     * @return void
     * @since  2016
     */
    public function reset(): void
    {
        $this->_trail = [];
    }

    /**
     * Appends an entry to the breadcrumb trail.
     *
     * @param  string  $title  Display label for the breadcrumb item.
     * @param  string  $link   URL for the item link; empty string for plain text (last item).
     * @return void
     * @since  2016
     */
    public function add(string $title, string $link = ''): void
    {
        $this->_trail[] = ['title' => $title, 'link' => $link];
    }

    /**
     * Renders the breadcrumb trail as an HTML ordered list with Schema.org markup.
     *
     * Each item is wrapped in <li> with BreadcrumbList / ListItem itemscope. Linked
     * items produce an <a> tag; unlinked items produce a plain <span>.
     *
     * @param  string|null  $separator  Unused; retained for API compatibility with other breadcrumb implementations.
     * @return string                   The complete HTML <ol> element.
     * @since  2016
     */
    public function trail($separator = null): string
    {
        $breadcrumb_count = 1;

        $trail_string = '<ol itemscope itemtype="http://schema.org/BreadcrumbList" class="breadcrumb">';

        for ($i = 0, $n = sizeof($this->_trail); $i < $n; $i++) {
            if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
                $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $this->_trail[$i]['link'] . '" itemprop="item"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span></a>';
            } else {
                $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span>';
            }
            $trail_string .= '<meta itemprop="position" content="' . $breadcrumb_count . '" /></li>' . PHP_EOL;
            $breadcrumb_count++;
        }

        return $trail_string . '</ol>';
    }
}
