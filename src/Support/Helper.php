<?php

namespace Sribna\Updater\Support;

/**
 * Trait Helper
 * @package Sribna\Updater\Support
 */
trait Helper
{

    /**
     * Whether a string matches a wildcard (with asterisk) patter
     * Taken from Laravel's Str::is()
     * @param string $string
     * @param string|array $pattern
     * @return bool
     */
    protected function is(string $string, $pattern): bool
    {
        if (is_array($pattern)) {
            foreach ($pattern as $p) {
                if ($this->is($string, $p)) {
                    return true;
                }
            }
            return false;
        }

        if ($string === $pattern) {
            return true;
        }

        $pattern = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return preg_match('#^' . $pattern . '\z#u', $string) === 1;
    }

    /**
     * Converts relative path to absolute using base path
     * @param string $path
     * @param string $basePath
     * @return string
     */
    protected function toAbsolutePath(string $path, string $basePath): string
    {
        if (strpos($path, $basePath) === 0) {
            return $path;
        }

        return rtrim($basePath, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . trim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Converts absolute path into relative to the base path
     * @param string $path
     * @param string $basePath
     * @return string
     */
    protected function toRelativePath(string $path, string $basePath): string
    {
        return trim(substr_replace($path, '', 0, strlen($basePath)), DIRECTORY_SEPARATOR);
    }

    /**
     * Make sure that all directory separators are the same
     * @param string $string
     * @param string $separator
     * @return string
     */
    protected function toSeparator(string $string, string $separator)
    {
        return str_replace(['\\', '/'], $separator, $string);
    }

    /**
     * Make sure that all directory separators are the same and remove slashes at the end
     * @param string|array $path
     * @param string $separator
     * @return string|array
     */
    protected function normalizePath($path, string $separator = DIRECTORY_SEPARATOR)
    {
        if (is_array($path)) {
            return array_map(function ($item) {
                return $this->normalizePath($item);
            }, $path);
        }

        return rtrim($this->toSeparator($path, $separator), $separator);
    }

}
