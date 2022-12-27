<?php

namespace App\Build;

// Adapted from https://github.com/andrefelipe/vite-php-setup/blob/master/public/helpers.php
class Vite
{
    public static function useVite(string $script = 'app.js'): void
    {
        self::jsPreloadImports($script);
        self::cssTag($script);
        self::register($script);
    }

    public static function isDev(): bool
    {
        return (bool) env('HMR_ENABLED');
    }

    public static function getTextDomain(): string
    {
        return wp_get_theme()->get('TextDomain') ?: 'sage';
    }

    public static function basePath(): string
    {
        return '/app/themes/' . self::themeName() . '/public/';
    }

    public static function register(string $entry)
    {
        $textDomain = self::getTextDomain();
        $url = self::isDev()
            ? 'http://localhost:3000/' . $entry
            : home_url() . self::assetUrl($entry);

        if (!$url) {
            return '';
        }

        wp_register_script("module/$textDomain/$entry", $url, false, true);
        wp_enqueue_script("module/$textDomain/$entry");
    }

    public static function getAssetFromManifest(string $asset): string
    {
        $manifest = self::getManifest();
        $assetPath = trim($asset, "'\"");

        if (!empty($manifest["../$assetPath"]['file'])) {
            return self::getPublicURLBase() . $manifest["../$assetPath"]['file'];
        }

        return $assetPath;
    }

    private static function themeName(): string
    {
        $uri = get_theme_file_uri();
        $offset = strripos($uri, '/');

        return substr($uri, $offset + 1);
    }

    private static function jsPreloadImports(string $entry)
    {
        $res = '';
        foreach (self::importsUrls($entry) as $url) {
            $res .= '<link rel="modulepreload" href="' . $url . '">';
        }

        add_action('wp_head', function () use (&$res) {
            echo $res;
        });
    }

    private static function cssTag(string $entry): string
    {
        // not needed on dev, it's injected by Vite
        if (self::isDev()) {
            return '';
        }

        $textDomain = self::getTextDomain();

        $tags = '';
        foreach (self::cssUrls($entry) as $url) {
            wp_register_style("$textDomain/$entry", $url);
            wp_enqueue_style("$textDomain/$entry", $url);
        }
        return $tags;
    }

    private static function getManifest(): array
    {
        static $manifestContent = null;

        if ($manifestContent !== null) {
            return $manifestContent;
        }

        $content = file_get_contents(get_template_directory() . '/public/manifest.json');

        return $manifestContent = json_decode($content, true);
    }

    private static function assetUrl(string $entry): string
    {
        $manifest = self::getManifest();

        return isset($manifest[$entry])
            ? self::basePath() . $manifest[$entry]['file']
            : self::basePath() . $entry;
    }

    private static function getPublicURLBase()
    {
        return self::isDev() ? '/public/' : home_url() . self::basePath();
    }

    private static function importsUrls(string $entry): array
    {
        $urls = [];
        $manifest = self::getManifest();

        if (!empty($manifest[$entry]['imports'])) {
            foreach ($manifest[$entry]['imports'] as $imports) {
                $urls[] = self::getPublicURLBase() . $manifest[$imports]['file'];
            }
        }
        return $urls;
    }

    private static function cssUrls(string $entry): array
    {
        $urls = [];
        $manifest = self::getManifest();

        if (!empty($manifest[$entry]['css'])) {
            foreach ($manifest[$entry]['css'] as $file) {
                $urls[] = self::getPublicURLBase() . $file;
            }
        }
        return $urls;
    }
}
