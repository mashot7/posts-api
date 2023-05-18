<?php

namespace Drupal\posts_api\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service responsible to fetch posts from 3rd party API.
 *
 * @package Drupal\posts_api\Services
 */
class FetchPostsService {

  /**
   * The HTTP client to fetch the posts.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Serialization for JSON.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected Json $json;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $loggerChannelFactory;

  /**
   * FetchPostsService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client to fetch the posts.
   * @param \Drupal\Component\Serialization\Json $json
   *   Serialization for JSON.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger channel.
   */
  public function __construct(
    ClientInterface $httpClient,
    Json $json,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {

    $this->httpClient = $httpClient;
    $this->json = $json;
    $this->loggerChannelFactory = $loggerChannelFactory->get('dependency_injection_exercise');
  }

  /**
   * Returns the default http client.
   *
   * @return \GuzzleHttp\Client
   *   A guzzle http client instance.
   */
  protected function httpClient(): ClientInterface {
    return $this->httpClient;
  }

  /**
   * Fetch posts from 3rd party API.
   *
   * @param int $userId
   *   Posts user ID.
   *
   * @return array
   *   Posts data array.
   *
   * @throws \Exception
   */
  public function getPosts(int $userId = 0): mixed {
    if ($userId === 0) {
      $userId = random_int(1, 20);
    }

    // Try to obtain the post data via the external API.
    try {
      $response = $this->httpClient()->request('GET', "https://jsonplaceholder.typicode.com/users/$userId/posts");
      $raw_data = $response->getBody()->getContents();
      $data = $this->json->decode($raw_data);
    }
    catch (GuzzleException $e) {
      $this->logger()->error($e->getMessage());
      $data = [];
    }

    return $data;
  }

  /**
   * Returns a channel logger object.
   *
   * @return \Drupal\Core\Logger\LoggerChannelInterface
   *   The logger for this channel.
   */
  protected function logger(): LoggerChannelInterface {
    return $this->loggerChannelFactory;
  }

}
