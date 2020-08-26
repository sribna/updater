<?php

namespace Sribna\Updater\Models;

use ArrayObject;
use Sribna\Updater\Contracts\ReleaseContract;
use Sribna\Updater\Contracts\ReleasesContract;

/**
 * Class Releases
 * @package Sribna\Updater\Models
 */
class Releases extends ArrayObject implements ReleasesContract
{

    /**
     * Sort by release version in ascending order
     * @return $this
     */
    public function sortByVersion()
    {
        $this->uasort(function (ReleaseContract $a, ReleaseContract $b) {
            return version_compare($a->getVersion(), $b->getVersion());
        });

        return $this;
    }

    /**
     * Filter by major version (assume format MAJOR.MINOR.PATCH)
     * @param string|int $majorVersion
     * @return $this
     */
    public function filterByMajorVersion($majorVersion)
    {
        foreach ($this as $index => $release) {
            /** @var Release $release */
            if (strpos($release->getVersion(), "$majorVersion.") !== 0) {
                $this->offsetUnset($index);
            }
        }

        return $this;
    }

    /**
     * Removes releases that are equal to or lower than the specified version
     * @param string $version
     * @return $this
     */
    public function filterByNewerVersion(string $version)
    {
        foreach ($this as $index => $release) {
            /** @var Release $release */
            if (version_compare($release->getVersion(), $version, '<=')) {
                $this->offsetUnset($index);
            }
        }

        return $this;
    }

    /**
     * Find the release by the version
     * @param string $version
     * @return Release|null
     */
    public function getReleaseByVersion(string $version): ?Release
    {
        foreach ($this as $release) {
            /** @var Release $release */
            if ($release->getVersion() === $version) {
                return $release;
            }
        }

        return null;
    }
}
