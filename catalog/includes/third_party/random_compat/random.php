<?php

declare (strict_types=1);
/**
 * Random_* Compatibility Library
 * for using the new PHP 7 random_* API in PHP 5 projects
 *
 * @version 2.0.4
 * @released 2016-11-07
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 - 2016 Paragon Initiative Enterprises
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
if (!defined('PHP_VERSION_ID')) {
    // This constant was introduced in PHP 5.2.7
    $random_compatversion = array_map(intval(...), explode('.', PHP_VERSION));
    define('PHP_VERSION_ID', $random_compatversion[0] * 10000 + $random_compatversion[1] * 100 + $random_compatversion[2]);
    $random_compatversion = null;
}