<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ThreadAndMirror\BlogBundle\Entity\Category;
use ThreadAndMirror\BlogBundle\Entity\Post;
use Stems\CoreBundle\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FrontController extends BaseFrontController
{
	/**
	 * Overview of all blog posts
	 *
	 * @Route("/magazine", name="thread_blog_front_index")
	 * @Template()
	 */
	public function indexAction()
	{
		$posts = $this->get('threadandmirror.blog.service.post')->getPublishedPostsForCategory(Category::ARTICLES, 6);

		return [
			'posts' => $posts
		];
	}

	/**
	 * Display a blog article
	 *
	 * @Route("/magazine/{slug}", name="thread_blog_front_article")
	 * @Template()
	 */
	public function articleAction(Post $post)
	{
		// Redirect to the index if the article isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'articles') {
			$this->redirect($this->generateUrl('thread_blog_front_index'));
		}

		// Prerender the sections, as referencing twig within itself causes a circular reference
		$sections = $this->get('stems.core.sections.manager')->setBundle('blog')->renderSections($post);

		return [
			'post' 	   => $post,
			'sections' => $sections
		];
	}

	/**
	 * Display an editor's picks post
	 *
	 * @Route("/editors-picks/{slug}", name="thread_blog_front_editors_pick")
	 * @Template()
	 */
	public function editorsPickAction(Post $post)
	{
		// Redirect to the index if the article isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'editors-picks') {
			$this->redirect($this->generateUrl('thread_blog_front_index'));
		}

		// Prerender the sections, as referencing twig within itself causes a circular reference
		$sections = $this->get('stems.core.sections.manager')->setBundle('blog')->renderSections($post);

		return [
			'post'     => $post,
			'sections' => $sections
		];
	}

	/**
	 * Preview a blog post that isn't published yet
	 *
	 * @Route("/blog/preview/{slug}", name="thread_blog_front_preview")
	 * @Template("ThreadAndMirrorBlogBundle:Front:article.html.twig")
	 */
	public function previewAction(Post $post)
	{
		if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
			throw new NotFoundHttpException();
		}

		$sections = $this->get('stems.core.sections.manager')->setBundle('blog')->renderSections($post);

		return [
			'post'             => $post,
			'sections'         => $sections,
			'disableAnalytics' => true
		];
	}

	/**
	 * Serves the blog as an rss feed
	 */
	public function rssAction()
	{
		// get all of the blog posts for the feed
		$posts = $this->get('threadandmirror.blog.service.post')->getPublishedPosts();

		// doctype
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';

		// rss header
		$xml .= '
			<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
			   <channel>
			      <title><![CDATA['.$this->container->getParameter('stems.site.name').']]></title>
			      <link>'.$this->container->getParameter('stems.site.url').'</link>
			      <description>'.$this->container->getParameter('stems.site.description').'</description>
			      <lastBuildDate>'.$posts[0]->getPublished()->format('r').'</lastBuildDate>
			      <language>en-us</language>
			      <webMaster>'.$this->container->getParameter('stems.site.email').' Webmaster</webMaster>
			      <copyright>Copyright '.date('Y').'</copyright>
			      <ttl>3600</ttl>
		';

		// add the posts
		foreach ($posts as &$post) {

			if ($post->getExcerpt()) {
				$title = $post->getTitle().' - '.$post->getExcerpt();
			} else {
				$title = $post->getTitle();
			}

			$xml .= '<item>';
			$xml .= '<title><![CDATA['.$title.']]></title>';
         	$xml .= '<author><![CDATA['.$this->container->getParameter('stems.site.name').']]></author>';
         	$xml .= '<link>'.$this->container->getParameter('stems.site.url').'/blog/'.$post->getSlug().'</link>';
         	$xml .= '<guid>'.$this->container->getParameter('stems.site.url').'/blog/'.$post->getSlug().'</guid>';
         	$xml .= '<category>fashion</category>';
         	$xml .= '<pubDate>'.$post->getPublished()->format('r').'</pubDate>';
         	$xml .= '<description><![CDATA['.$post->getExcerpt().']]></description>';
         	$xml .= '<media:thumbnail url="'.$this->container->getParameter('stems.site.url').'/'.$post->getImage().'" />';

			$xml .= '</item>';
		}

		// rss closure
		$xml .= '</channel></rss>';

		// create the response and set the type as xml
		$response = new Response($xml);
		$response->headers->set('Content-Type', 'text/xml');

		return $response;
	}
}
