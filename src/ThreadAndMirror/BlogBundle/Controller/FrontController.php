<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use ThreadAndMirror\BlogBundle\Entity\Post;
use Stems\CoreBundle\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\BlogBundle\Entity\Comment;
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
	public function indexAction(Request $request)
	{
		$chunk = $this->container->getParameter('threadandmirror.blog.index.chunk_size');

		// Get posts for the view
		$posts = $this->em->getRepository('ThreadAndMirrorBlogBundle:Post')->findLatest($chunk);

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
		// Redirect to the index if the collection isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'articles') {
			$this->redirect($this->generateUrl('thread_blog_front_list'));
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
		// Redirect to the index if the collection isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'editors-picks') {
			$this->redirect($this->generateUrl('thread_blog_front_list'));
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
	 * @param  $slug 	string 		The slug of the requested blog post
	 */
	public function previewAction($slug)
	{
		// Redirect if the user isn't at least an admin
		if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
			return $this->redirect('/');
		}

		// Get the requested post
		$post = $this->em->getRepository('ThreadAndMirrorBlogBundle:Post')->findPublishedPost($slug);

		// Set the dynamic page values
		$this->page->setTitle($post->getTitle());
		$this->page->setWindowTitle($post->getTitle().' - '.$post->getExcerpt());
		$this->page->setmetaKeywords($post->getMetaKeywords());
		$this->page->setMetaDescription($post->getMetaDescription());
		$this->page->setDisableAnalytics(true);

		// Pre-render the sections, as referencing twig within itself causes a circular reference
		$sections = array();

		foreach ($post->getSections() as $link) {
			$sections[] = $this->get('stems.core.sections.manager')->setBundle('blog')->renderSection($link);
		}

		// Build the comment form
		$form = $this->createForm('blog_comment_type', new Comment());

		return $this->render('ThreadAndMirrorBlogBundle:Front:post.html.twig', array(
//			'page'		=> $this->page,
			'post' 		=> $post,
			'sections' 	=> $sections,
			'form' 		=> $form->createView(),
		));
	}

	/**
	 * Serves the blog as an rss feed
	 */
	public function rssAction()
	{
		// get all of the blog posts for the feed
		$posts = $this->em->getRepository('ThreadAndMirrorBlogBundle:Post')->findBy(array('deleted' => false, 'status' => 'Published'), array('published' => 'DESC'));

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
