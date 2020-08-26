<?php

namespace Sribna\Updater\Support;

use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * Class Backup
 *
 * @method string getDirectory()
 * @method string getSourceDirectory()
 * @method string getFilename()
 * @method array getExclusions()
 * @method string getFilenameDateFormat()
 * @method int getAddedCount()
 *
 * @method $this setDirectory(string $directory)
 * @method $this setSourceDirectory(string $directory)
 * @method $this setFilename(string $filename)
 * @method $this setExclusions(array $excludedPaths)
 * @method $this setFilenameDateFormat(string $datetimeFormat)
 */
class Backup
{

    use Magic;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Zip
     */
    protected $zip;

    /**
     * @var string The directory path that holds all backup files
     */
    protected $directory;

    /**
     * @var string The directory path to make backup from
     */
    protected $sourceDirectory;

    /**
     * @var string The absolute path to the backup file
     */
    protected $filename;

    /**
     * @var array An array of file paths to be excluded from backup
     */
    protected $exclusions = [];

    /**
     * Datetime format for backup files
     * @var string
     */
    protected $filenameDateFormat = 'Y_m_d_His';

    /**
     * @var int Number of files saved in the backup file
     */
    protected $addedCount = 0;

    /**
     * Backup constructor.
     */
    public function __construct()
    {
        $this->zip = new Zip();
        $this->file = new File();
    }

    /**
     * Create a backup file
     * @return $this
     * @throws Throwable
     */
    public function create()
    {
        $this->filename = $this->filename ?: $this->makeFilename();

        if (file_exists($this->filename)) {
            throw new RuntimeException("Backup file {$this->filename} already exists");
        }

        try {

            $items = $this->file->setDirectory($this->sourceDirectory)
                ->setExclusions($this->exclusions)
                ->scan();

            $this->addedCount = $this->zip->setFilename($this->filename)
                ->create($items, $this->sourceDirectory)
                ->getAddedCount();

        } catch (Throwable $exception) {
            if ($this->filename && file_exists($this->filename)) {
                @unlink($this->filename);
            }
            throw $exception;
        }

        return $this;
    }

    /**
     * Returns an array of backup files in the directory. Latest files go first
     * @return array
     */
    public function all(): array
    {
        if (!$this->directory || !is_dir($this->directory)) {
            throw new UnexpectedValueException('Invalid backup directory path');
        }

        if (($items = glob(rtrim($this->directory, '/\\') . '/*.zip')) === false) {
            throw new UnexpectedValueException("Failed to list files in {$this->directory}");
        }

        uasort($items, function ($a, $b) {
            return filectime($a) < filectime($b); // Latest first
        });

        return $items;
    }

    /**
     * Keep max allowed number of backup files. Excess older files will be deleted
     * @param int $maxFilesToKeep
     * @return $this
     */
    public function reduce(int $maxFilesToKeep)
    {
        $all = $this->all();
        $count = count($all);

        if ($count > $maxFilesToKeep) {
            foreach (array_slice($all, -($count - $maxFilesToKeep)) as $file) {
                @unlink($file);
            }
        }

        return $this;
    }

    /**
     * Make backup filename based on the current date
     * @return string
     */
    public function makeFilename(): string
    {
        if (!$this->directory || !is_dir($this->directory)) {
            throw new UnexpectedValueException('Invalid backup directory path');
        }

        return rtrim($this->directory, '/\\')
            . DIRECTORY_SEPARATOR
            . date($this->filenameDateFormat) . '.zip';
    }

}
