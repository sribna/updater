<?php

namespace Sribna\Updater\Models;

use Sribna\Updater\Contracts\ReleaseContract;

/**
 * Class Release
 * @package Sribna\Updater\Models
 */
class Release implements ReleaseContract
{

    /**
     * Release ID
     * @var mixed
     */
    private $id;

    /**
     * Release version
     * @var string
     */
    private $version;

    /**
     * Date when the release is created
     * @var mixed
     */
    private $created_at;

    /**
     * Release file MD5 hash
     * @var string
     */
    private $hash;

    /**
     * Release file size, in bytes
     * @var int
     */
    private $size;

    /**
     * Release notes
     * @var string
     */
    private $text;

    /**
     * Release download URL
     * @var string
     */
    private $download_url;

    /* Getters */

    /**
     * Returns the release ID
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the release version
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Returns the release creation date
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Returns the release file MD5 hash
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Returns the release file size
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the release notes
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Returns the release download URL
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->download_url;
    }

    /* Setters */

    /**
     * Sets the release ID
     * @param $releaseId
     * @return $this
     */
    public function setId($releaseId)
    {
        $this->id = $releaseId;
        return $this;
    }

    /**
     * Sets the release version
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Sets the release creation date
     * @param mixed $creationDate
     * @return $this
     */
    public function setCreatedAt($creationDate)
    {
        $this->created_at = $creationDate;
        return $this;
    }

    /**
     * Sets the release MD5 hash
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Sets the release file size, in bytes
     * @param int $size
     * @return $this
     */
    public function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Sets the release notes
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Sets the release download URL
     * @param string $url
     * @return $this
     */
    public function setDownloadUrl(string $url)
    {
        $this->download_url = $url;
        return $this;
    }

}
