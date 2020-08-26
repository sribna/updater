<?php

namespace Sribna\Updater\Sources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use JsonMapper;
use JsonMapper_Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Sribna\Updater\Contracts\SourceContract;
use Sribna\Updater\Models\Release;
use Sribna\Updater\Models\Releases;
use UnexpectedValueException;

/**
 * Class BaseSource
 * @package Sribna\Updater\Sources
 */
abstract class BaseSource implements SourceContract
{
    /**
     * The current app version
     * @var string
     */
    private $currentVersion;

    /**
     * An array of request options
     * @link http://docs.guzzlephp.org/en/stable/request-options.html
     * @var array
     */
    private $requestOptions = [];

    /**
     * The request URI
     * @var string|array
     */
    private $requestUri;

    /**
     * An instance of Guzzle HTTP client
     * @link http://guzzlephp.org
     * @var Client
     */
    private $httpClient;

    /**
     * Returns an HTTP client instance
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Sets an HTTP client instance
     * @param Client $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Returns an array of request options
     * @return array
     */
    public function getRequestOptions(): array
    {
        return $this->requestOptions;
    }

    /**
     * Sets the request options
     * @param array $options
     * @return $this
     */
    public function setRequestOptions(array $options)
    {
        $this->requestOptions = $options;
        return $this;
    }

    /**
     * Returns the request URI(s)
     * @return string|array
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Sets the request URI
     * @param string|array $uri
     * @return $this
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
        return $this;
    }

    /**
     * Returns the current app version
     * @return string
     */
    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    /**
     * Sets the current app version
     * @param string $version
     * @return $this
     */
    public function setCurrentVersion(string $version)
    {
        $this->currentVersion = $version;
        return $this;
    }

    /**
     * Returns the current major version
     * @return string
     */
    public function getCurrentMajorVersion()
    {
        return $this->getMajorVersion($this->getCurrentVersion());
    }

    /**
     * Returns the first part of a version
     * @param $version
     * @return string
     */
    public function getMajorVersion($version)
    {
        return strtok($version, '.');
    }

    /**
     * Performs an HTTP request
     * @param string $method
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(string $method = 'POST'): ResponseInterface
    {
        $client = $this->getHttpClient();

        foreach ((array)$this->getRequestUri() as $uri) {
            try {
                return $client->request($method, $uri, $this->getRequestOptions());
            } catch (ConnectException $exception) {
                continue;
            }
        }

        throw new RuntimeException("Couldn't connect to any host");
    }

    /**
     * Converts a release JSON array to a Release model
     * @param array $json
     * @return mixed
     * @throws JsonMapper_Exception
     */
    public function toRelease(array $json): Release
    {
        return (new JsonMapper())->map($json, new Release());
    }

    /**
     * Converts an array of release JSON arrays to a Releases model
     * @param array $json
     * @return Releases
     * @throws JsonMapper_Exception
     */
    public function toReleases(array $json): Releases
    {
        $releases = new Releases();

        foreach ($json as $releaseJson) {
            $release = $this->toRelease($releaseJson);
            $releases->offsetSet($release->getId(), $release);
        }

        return $releases;
    }

    /**
     * Returns the Releases object from JSON received in the HTTP response object
     * @param ResponseInterface $response
     * @return Releases
     * @throws JsonMapper_Exception
     */
    public function getReleasesFromJsonResponse(ResponseInterface $response): Releases
    {
        return $this->toReleases($this->validateJsonResponse($response))
            ->filterByMajorVersion($this->getCurrentMajorVersion())
            ->filterByNewerVersion($this->getCurrentVersion())
            ->sortByVersion();
    }

    /**
     * Validates token in the "Authorization" header
     * @param ResponseInterface $response
     * @param string $knownToken
     */
    protected function validateBasicAuthorization(ResponseInterface $response, string $knownToken)
    {
        if (!($header = trim($response->getHeaderLine('Authorization')))) {
            throw new UnexpectedValueException("Authorization header is not set");
        }

        if (!hash_equals($knownToken, trim(preg_replace('/^Basic/', '', $header)))) {
            throw new UnexpectedValueException("Invalid token received in the Authorization header");
        }
    }

    /**
     * Validates and returns an array of data from JSON response
     * @param ResponseInterface $response
     * @return array
     */
    protected function validateJsonResponse(ResponseInterface $response): array
    {
        if (!is_array($decoded = json_decode($response->getBody()->getContents(), true))) {
            throw new UnexpectedValueException("Failed to decode JSON in the response");
        }
        return $decoded;
    }
}
