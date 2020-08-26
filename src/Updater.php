<?php

namespace Sribna\Updater;

use RuntimeException;
use Sribna\Updater\Support\Backup;
use Sribna\Updater\Support\File;
use Sribna\Updater\Support\Helper;
use Sribna\Updater\Support\Magic;
use Sribna\Updater\Support\Zip;
use Throwable;
use UnexpectedValueException;

/**
 * Class Updater
 *
 * @method Zip getZip()
 * @method Backup getBackup()
 * @method File getFile()
 *
 * @method string getSourceFilename()
 * @method string getSkipLevels()
 * @method string getPath()
 * @method string getBackupDirectory()
 * @method string getBackupFilename()
 * @method array getBackupExclusions()
 * @method array getPurgeExclusions()
 * @method array getHooks()
 * @method array getExtractExclusions()
 * @method int getDeletedCount()
 * @method int getExtractedCount()
 * @method int getBackupCount()
 *
 * @method $this setSourceFilename(string $filename)
 * @method $this setSkipLevels(string $string)
 * @method $this setPath(string $path)
 * @method $this setBackupDirectory(string $path)
 * @method $this setBackupFilename(string $path)
 * @method $this stringBackupExclusions(array $files)
 * @method $this setBackupExclusions(array $paths)
 * @method $this setPurgeExclusions(array $paths)
 * @method $this setHooks(array $hooks)
 * @method $this setExtractExclusions(array $relativePaths)
 */
class Updater
{

    use Magic, Helper;

    /**
     * @var Zip
     */
    protected $zip;

    /**
     * @var Backup
     */
    protected $backup;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var string The full path to source ZIP file
     */
    protected $sourceFilename;

    /**
     * @var string How many levels to skip in ZIP archive
     */
    protected $skipLevels;

    /**
     * @var string Base path containing the files to update
     */
    protected $path;

    /**
     * @var string The full path to the directory that contains all backups
     */
    protected $backupDirectory;

    /**
     * @var string The full path to the backup ZIP file
     */
    protected $backupFilename;

    /**
     * @var array An array of files to exclude from the backup
     */
    protected $backupExclusions = [];

    /**
     * @var array An array of files to not delete
     */
    protected $purgeExclusions = [];

    /**
     * @var array An array of relative (to root) paths to not overwrite while ZIP extraction
     */
    protected $extractExclusions = [];

    /**
     * @var int Number of deleted files
     */
    protected $deletedCount = 0;

    /**
     * @var int Number of extracted files
     */
    protected $extractedCount = 0;

    /**
     * @var int Number of files added to a backup file
     */
    protected $backupCount = 0;

    /**
     * Updater constructor.
     */
    public function __construct()
    {
        $this->zip = new Zip();
        $this->file = new File();
        $this->backup = new Backup();
    }

    /**
     * Run full update process
     * @return $this
     * @throws Throwable
     */
    public function full()
    {
        return $this->check()
            ->backup()
            ->purge()
            ->extract();
    }

    /**
     * Returns an array of zip files
     * @return array|null
     */
    public function readZip()
    {
        return $this->zip->setFilename($this->sourceFilename)
            ->setSkipLevels($this->skipLevels)
            ->read($this->extractExclusions)
            ->getContent();
    }

    /**
     * Check if files from archive file can overwrite files in the directory
     * @return $this
     * @throws Throwable
     */
    public function check()
    {
        if (!$this->path || !is_dir($this->path)) {
            throw new UnexpectedValueException("Invalid directory {$this->path}");
        }

        if (!($content = $this->readZip())) {
            throw new UnexpectedValueException('ZIP file is empty');
        }

        foreach ($content as $file) {
            $file = $this->toAbsolutePath($file, $this->path);
            if (file_exists($file) && !is_writable($file)) {
                throw new RuntimeException("File $file is not writable");
            }
        }

        return $this;
    }

    /**
     * Creates a backup file
     * @return $this
     * @throws Throwable
     */
    public function backup()
    {
        $backup = $this->backup->setSourceDirectory($this->path)
            ->setExclusions($this->backupExclusions)
            ->setDirectory($this->backupDirectory)
            ->create();

        $this->backupFilename = $backup->getFilename();
        $this->backupCount = $this->backup->getAddedCount();

        return $this;
    }

    /**
     * Purge all files in the directory except excluded paths
     * @return $this
     * @throws Throwable
     */
    public function purge()
    {
        $this->deletedCount = $this->file->setDirectory($this->path)
            ->setExclusions($this->purgeExclusions)
            ->delete()
            ->getDeletedCount();

        return $this;
    }

    /**
     * Extract files from archive file
     * @return $this
     * @throws Throwable
     */
    public function extract()
    {
        $this->extractedCount = $this->zip->setFilename($this->sourceFilename)
            ->setSkipLevels($this->skipLevels)
            ->extract($this->path, $this->extractExclusions)
            ->getExtractedCount();

        return $this;
    }
}
