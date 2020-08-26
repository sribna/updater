<?php

namespace Sribna\Updater\Sources;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonMapper_Exception;
use RuntimeException;
use Sribna\Updater\Models\Release;
use Sribna\Updater\Models\Releases;
use Sribna\Updater\Updater;
use Throwable;

/**
 * Class CommonSource
 * @package Sribna\Updater\Sources
 */
class CommonSource extends BaseSource
{

    /**
     * The authorization token
     * @var string
     */
    private $token;

    /**
     * The Updater instance
     * @var Updater
     */
    private $updater;

    /**
     * Returns the authorization token
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Sets the authorization token
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returns the Updater instance
     * @return Updater
     */
    public function getUpdater(): Updater
    {
        return $this->updater;
    }

    /**
     * Sets the Updater instance
     * @param Updater $updater
     * @return $this
     */
    public function setUpdater(Updater $updater)
    {
        $this->updater = $updater;
        return $this;
    }

    /**
     * Returns all releases
     * @return Releases
     * @throws GuzzleException
     * @throws JsonMapper_Exception
     */
    public function all(): Releases
    {
        $response = $this->request();

        if (!is_null($token = $this->getToken())) {
            $this->validateBasicAuthorization($response, $token);
        }

        return $this->getReleasesFromJsonResponse($response);
    }

    /**
     * Fetch the closest update release or find the specified version
     * @param string|null $version
     * @return Release|null
     * @throws GuzzleException
     * @throws JsonMapper_Exception
     */
    public function get(string $version = null): ?Release
    {
        $releases = $this->all();

        if ($version) {
            return $releases->getReleaseByVersion($version);
        }

        // Get first release
        $iterator = $releases->getIterator();
        $iterator->rewind();
        return $iterator->current() ?: null;
    }

    /**
     * Download the release
     * @param Release $release
     * @param string $destination
     * @return string Path to downloaded release
     * @throws GuzzleException
     */
    public function download(Release $release, string $destination): string
    {
        $destination = $this->prepareDestination($destination);
        $response = $this->request();

        if (file_put_contents($destination, $response->getBody()->getContents()) === false) {
            throw new RuntimeException("Failed to save to $destination");
        }

        return $destination;
    }

    /**
     * Whether a newer release available
     * @return bool
     * @throws GuzzleException
     * @throws JsonMapper_Exception
     */
    public function isNewerVersionAvailable(): bool
    {
        return $this->all()->count() > 0;
    }

    /**
     * Performs the update process
     * @param string $stage
     * @return Updater
     * @throws Throwable
     */
    public function update(string $stage): Updater
    {
        $updater = $this->getUpdater();

        switch ($stage) {
            case 'full':
                return $updater->full();
            case 'check':
                return $updater->check();
            case 'backup':
                return $updater->backup();
            case 'purge':
                return $updater->purge();
            case 'extract':
                return $updater->extract();
            default:
                throw new InvalidArgumentException("Unknown update stage $stage");
        }
    }

    /**
     * Prepare the file destination
     * @param string $destination
     * @param int $chmod
     * @param string $prefix
     * @return string
     */
    protected function prepareDestination(string $destination, $chmod = 0755, $prefix = 'UPD'): string
    {
        if (pathinfo($destination, PATHINFO_EXTENSION)) {
            if (is_file($destination)) {
                throw new RuntimeException("File $destination already exists");
            }
            return $destination;
        }

        if (!is_dir($destination) && !mkdir($destination, $chmod, true)) {
            throw new RuntimeException("Failed to create directory $destination");
        }

        if (!($destination = tempnam($destination, $prefix))) {
            throw new RuntimeException("Failed to create filename for $destination");
        }

        return $destination;
    }
}
