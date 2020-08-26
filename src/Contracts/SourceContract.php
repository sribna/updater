<?php

namespace Sribna\Updater\Contracts;

use Sribna\Updater\Models\Release;
use Sribna\Updater\Models\Releases;
use Sribna\Updater\Updater;

/**
 * Interface SourceContract
 * @package Sribna\Updater\Contracts
 */
interface SourceContract
{
    /**
     * Returns all available updates for the current version
     * @return Releases
     */
    public function all(): Releases;

    /**
     * Returns the closest update release or a specific release version
     * @param string|null $version
     * @return Release|null
     */
    public function get(string $version = null): ?Release;

    /**
     * Check if a newer version than the one installed is available
     * @return bool
     */
    public function isNewerVersionAvailable(): bool;

    /**
     * Downloads the release
     * @param Release $release
     * @param string $destination
     * @return string
     */
    public function download(Release $release, string $destination): string;

    /**
     * Performs the update process
     * @param string $stage
     * @return Updater
     */
    public function update(string $stage): Updater;
}
