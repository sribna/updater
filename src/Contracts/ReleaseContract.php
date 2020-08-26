<?php

namespace Sribna\Updater\Contracts;

/**
 * Interface ReleaseContract
 * @package Sribna\Updater\Contracts
 */
interface ReleaseContract
{
    /* Getters */

    /**
     * Returns the release ID
     * @return mixed
     */
    public function getId();

    /**
     * Returns the release version
     * @return string
     */
    public function getVersion(): string;

    /**
     * Returns the release creation date
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns the release MD5 hash
     * @return string
     */
    public function getHash(): string;

    /**
     * Returns the release file size, in bytes
     * @return int
     */
    public function getSize(): int;

    /**
     * Returns the release notes
     * @return string
     */
    public function getText(): string;

    /**
     * Returns the release download URL
     * @return string
     */
    public function getDownloadUrl(): string;

    /* Setters */

    /**
     * Sets the release ID
     * @param $releaseId
     */
    public function setId($releaseId);

    /**
     * Sets the release version
     * @param string $version
     */
    public function setVersion(string $version);

    /**
     * Sets the release creation date
     * @param mixed $creationDate
     */
    public function setCreatedAt($creationDate);

    /**
     * Sets the release MD5 hash
     * @param string $hash
     */
    public function setHash(string $hash);

    /**
     * Sets the release file size, in bytes
     * @param int $size
     */
    public function setSize(int $size);

    /**
     * Sets the release notes
     * @param string $text
     */
    public function setText(string $text);

    /**
     * Sets the release download URL
     * @param string $url
     */
    public function setDownloadUrl(string $url);

}
