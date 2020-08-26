<?php

namespace Sribna\Updater\Support;

use Closure;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * Class File
 *
 * @method string getDirectory()
 * @method int getDeletedCount()
 * @method $this setIterator(RecursiveIteratorIterator $iterator)
 * @method $this setDirectory(string $directory)
 * @method $this setExclusions(array $excludedPaths)
 */
class File
{
    use Magic, Helper;

    /**
     * @var RecursiveIteratorIterator
     */
    protected $iterator;

    /**
     * @var string The directory path for scanning
     */
    protected $directory;

    /**
     * @var int Number of deleted files
     */
    protected $deletedCount = 0;

    /**
     * @var array An array of excluded for scanning paths
     */
    protected $exclusions = [];

    /**
     * Recursively scans the directory
     * @return RecursiveIteratorIterator
     */
    public function scan()
    {
        return $this->getIterator(RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Recursive delete all files in the directory
     * @return $this
     */
    public function delete()
    {
        $this->deletedCount = 0;

        /** @var SplFileInfo $file */
        foreach ($this->getIterator(RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                $this->deletedCount += (int)@rmdir($file->getPathname());
            } else {
                $this->deletedCount += (int)@unlink($file->getPathname());
            }
        }

        return $this;
    }

    /**
     * Returns an instance of recursive iterator with scanned files
     * @param int $mode
     * @return RecursiveIteratorIterator
     */
    public function getIterator($mode = RecursiveIteratorIterator::LEAVES_ONLY): RecursiveIteratorIterator
    {
        if (isset($this->iterator)) {
            return $this->iterator;
        }

        if (!$this->directory || !is_dir($this->directory)) {
            throw new UnexpectedValueException("Invalid directory {$this->directory}");
        }

        return $this->iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($this->directory,
                    RecursiveDirectoryIterator::SKIP_DOTS), $this->getFilter()), $mode);
    }

    /**
     * Filter callback
     * @return Closure
     */
    protected function getFilter(): Closure
    {
        return function (SplFileInfo $file) {
            return !$this->is($file->getPathname(), $this->normalizePath($this->exclusions));
        };
    }

}
