<?php
/**
 * Lightweight Translation Engine
 */

namespace App\Core {

    class Lang
    {
        private static array $strings = [];
        private static string $locale = 'ar';
        private static array $languages = [];

        /**
         * Initialize with a locale — loads the matching lang file
         */
        public static function init(string $locale): void
        {
            self::$languages = require __DIR__ . '/../Config/languages.php';

            if (!isset(self::$languages[$locale])) {
                $locale = 'ar'; // fallback
            }

            self::$locale = $locale;

            $langFile = __DIR__ . '/../Lang/' . $locale . '.php';
            if (file_exists($langFile)) {
                self::$strings = require $langFile;
            }
        }

        /**
         * Get a translated string by dot-notation key
         * Supports parameter substitution: __('validation.max', ['label' => 'Name', 'length' => 50])
         */
        public static function get(string $key, array $params = []): string
        {
            $text = self::$strings[$key] ?? $key;

            foreach ($params as $name => $value) {
                $text = str_replace(':' . $name, (string) $value, $text);
            }

            return $text;
        }

        /** Current locale code (e.g. 'ar', 'en') */
        public static function locale(): string
        {
            return self::$locale;
        }

        /** Text direction: 'rtl' or 'ltr' */
        public static function dir(): string
        {
            return self::$languages[self::$locale]['dir'] ?? 'rtl';
        }

        /** Whether current language is RTL */
        public static function isRtl(): bool
        {
            return self::dir() === 'rtl';
        }

        /** Font family for current language */
        public static function fontFamily(): string
        {
            return self::$languages[self::$locale]['font'] ?? 'Alexandria';
        }

        /** All supported languages */
        public static function available(): array
        {
            return self::$languages;
        }

        /** All languages except the current one (for the switcher UI) */
        public static function otherLanguages(): array
        {
            return array_filter(self::$languages, function ($code) {
                return $code !== self::$locale;
            }, ARRAY_FILTER_USE_KEY);
        }
    }

}

namespace {

    /**
     * Global translation shortcut
     */
    function __($key, $params = [])
    {
        return \App\Core\Lang::get($key, $params);
    }

}
