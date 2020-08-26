<?php

namespace Sribna\Updater\Support;

use RuntimeException;
use Traversable;
use UnexpectedValueException;
use ZipArchive;

/**
 * Class Zip
 *
 * @method string|null getFilename()
 * @method ZipArchive|null getZip
 * @method array|null getContent()
 * @method int getSkipLevels()
 * @method int getFilePermission()
 * @method int getDirectoryPermission()
 * @method int getExtractedCount()
 * @method int getAddedCount()
 *
 * @method $this setFilename(string $filename)
 * @method $this setZip(ZipArchive $zip)
 * @method $this setContent(array $content)
 * @method $this setSkipLevels(int $levels)
 * @method $this setFilePermission(int $permissions)
 * @method $this setDirectoryPermission(int $permissions)
 * @method $this setExtractedCount(int $count)
 * @method $this setAddedCount(int $count)
 */
class Zip
{

    use Magic, Helper;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int File permission for extracted files
     */
    protected $filePermission = 0644;

    /**
     * @var int File permissions for extracted directories
     */
    protected $directoryPermission = 0755;

    /**
     * @var ZipArchive
     */
    protected $zip;

    /**
     * @var array
     */
    protected $content;

    /**
     * @var int
     */
    protected $skipLevels = 0;

    /**
     * @var int
     */
    protected $extractedCount = 0;

    /**
     * @var int
     */
    protected $addedCount = 0;

    /**
     * @param array $exclusions
     * @return $this
     */
    public function read(array $exclusions = [])
    {
        $this->open();

        $this->content = [];

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            if ($path = $this->skipLevels($this->zip->getNameIndex($i))) {
                if (!$this->is($path, $exclusions)) {
                    $this->content[] = $path;
                }
            }
        }

        $this->close();
        return $this;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function skipLevels(string $path)
    {
        return $this->skipLevels
            ? implode('/', array_slice(explode('/', $path), $this->skipLevels))
            : $path;
    }

    /**
     * @param null $flags
     * @return $this
     */
    public function open($flags = null)
    {
        $this->zip = new ZipArchive();
        if ($this->zip->open($this->filename, $flags) !== true) {
            throw new UnexpectedValueException("Failed to open ZIP file {$this->filename}");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        if ($this->zip instanceof ZipArchive) {
            $this->zip->close();
        }

        return $this;
    }

    /**
     * @param Traversable $files
     * @param string|null $basePath
     * @return $this
     */
    public function create(Traversable $files, string $basePath = null)
    {
        $this->open(ZipArchive::CREATE);

        $this->addedCount = 0;

        foreach ($files as $file) {

            // It's recommended to use "/" as directory separator in ZIP filenames
            $path = $this->toSeparator(($basePath ? $this->toRelativePath($file, $basePath) : $file), '/');

            if (is_dir($file)) {
                $this->addedCount += (int)$this->zip->addEmptyDir($path);
            } else {
                $this->addedCount += (int)$this->zip->addFile($file, $path);
            }
        }

        if ($this->zip->numFiles !== iterator_count($files)) {
            throw new UnexpectedValueException("One or more items have not been added to {$this->filename}");
        }

        $this->close();

        if (!file_exists($this->filename)) {
            throw new RuntimeException("Failed to create {$this->filename}");
        }

        return $this;
    }

    /**
     * @param string $destination
     * @param array $exclusions
     * @return $this
     */
    public function extract(string $destination, array $exclusions = [])
    {
        $this->open();

        $this->extractedCount = 0;
        $destination = rtrim($destination, '/\\');

        for ($i = 0; $i < $this->zip->numFiles; $i++) {

            if (!($path = $this->skipLevels($this->zip->getNameIndex($i)))) {
                continue;
            }

            if ($this->is($path, $exclusions)) {
                continue;
            }

            $filename = $destination . DIRECTORY_SEPARATOR . $path;

            if (substr($path, -1) === '/') {
                if (!is_dir($filename) && !mkdir($filename, $this->directoryPermission, true)) {
                    throw new RuntimeException("Failed to create directory $filename while extracting $path");
                }
            } else {
                if (file_put_contents($filename, $this->zip->getFromIndex($i)) === false) {
                    throw new UnexpectedValueException("Failed to save $filename");
                }
                chmod($filename, $this->filePermission);
            }

            $this->extractedCount++;
        }

        $this->close();
        return $this;
    }
}
