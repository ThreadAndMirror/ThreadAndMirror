<?php

namespace ThreadAndMirror\BlogBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Service\SitemapListenerInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use ThreadAndMirror\BlogBundle\Service\PostService;

class SitemapListener implements SitemapListenerInterface
{
	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @var PostService
	 */
	private $postService;

	/**
	 * @param RouterInterface $router
	 * @param PostService $postService
	 */
	public function __construct(RouterInterface $router, PostService $postService)
	{
		$this->router = $router;
		$this->postService = $postService;
	}

	public function populateSitemap(SitemapPopulateEvent $event)
	{
		$posts = $this->postService->getPublishedPosts();

		foreach ($posts as $post) {

			switch ($post->getCategory()->getSlug())
			{
				case 'editors-picks':
					$route = 'thread_blog_front_editors_pick';
					break;
				case 'articles':
					$route = 'thread_blog_front_article';
					break;
			}

			$url = $this->router->generate($route, ['slug' => $post->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

			$sitemapUrl = new UrlConcrete($url, $post->getUpdated(), UrlConcrete::CHANGEFREQ_MONTHLY);

			$event->getUrlContainer()->addUrl($sitemapUrl, 'blog');
		}
	}
}