<?php

namespace Drupal\posts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\posts_api\Services\FetchPostsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the rest output.
 */
class DisplayPostsController extends ControllerBase {

  /**
   * Service responsible to fetch posts from 3rd party API.
   *
   * @var \Drupal\posts_api\Services\FetchPostsService
   */
  private FetchPostsService $postsService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    FetchPostsService $postsService
  ) {
    $this->postsService = $postsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('posts_api.fetch_posts')
    );
  }

  /**
   * Displays the posts.
   *
   * @return array[]
   *   A renderable array representing the posts.
   *
   * @throws \Exception
   */
  public function showPosts($user_id): array {
    // Setup build caching.
    $build = [
      '#cache' => [
        'max-age' => 60,
        'contexts' => [
          'url',
        ],
      ],
    ];

    $data = $this->postsService->getPosts($user_id);

    if (!$data) {
      $build['error'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => t('No posts available.'),
      ];

      return $build;
    }

    // Build a listing of posts from the post data.
    $build['posts'] = array_map(static function ($item) {
      return [
        '#theme' => 'posts_api_post',
        '#userId' => $item['userId'],
        '#id' => $item['id'],
        '#title' => $item['title'],
        '#body' => $item['body'],
      ];
    }, $data);

    return $build;
  }

}
