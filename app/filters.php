<?php

/**
 * Theme filters.
 */

namespace App;

use App\Build\Vite;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Load scripts asset as module
 *
 * @return string
 */
add_filter('script_loader_tag', function ($tag, $handle) {
    if (Vite::isDev()) {
        $textDomain = Vite::getTextDomain();

        if (str_contains($handle, "module/$textDomain/")) {
            $str = "<script type='module'";
            $str .= ' crossorigin ';
            $tag = str_replace("<script ", $str, $tag);
        }
    }
    return $tag;
}, 10, 2);
