<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Stems\BlogBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stems\CoreBundle\Controller\BaseFrontController;

class FrontController extends BaseFrontController
{
	/**
	 * Display a list of editor's picks posts
	 *
	 * @Route("/editors-picks", name="thread_editorspicks_front_list")
	 * @Template()
	 */
	public function listAction(Request $request)
	{
		$chunk = $this->container->getParameter('stems.blog.index.chunk_size');

		// Get posts for the view
		$posts = $this->em->getRepository('StemsBlogBundle:Post')->findPublishedPostsByCategory('editors-picks', $chunk);

		if ($this->container->getParameter('stems.blog.index.list_style') == 'sequential') {

			// Gather render sections for each of the posts
			$sections = $this->get('stems.core.sections.manager')->setBundle('blog')->renderCollection($posts);

			return $this->render('StemsBlogBundle:Front:sequential.html.twig', array(
				'posts' 		=> $posts,
				'sections' 		=> $sections,
				'page'			=> $this->page,
			));

		} else {

			// Paginate the result
			$data = $this->get('stems.core.pagination')->paginate($posts, $request, array('maxPerPage' => $chunk));

			return $this->render('StemsBlogBundle:Front:list.html.twig', array(
				'posts' 		=> $data,
				'page'			=> $this->page,
			));
		}
	}

	/**
	 * Display an editor's picks post
	 *
	 * @Route("/editors-picks/{slug}", name="thread_editorspicks_front_post")
	 * @Template()
	 */
	public function postAction(Post $post)
	{
		// Redirect to the index if the collection isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'editors-picks') {
			$this->redirect($this->generateUrl('thread_editorspicks_front_list'));
		}

		// Load the page object from the CMS
		$this->loadPage('editors-picks/{slug}', array(
			'title' 			=> $post,
			'windowTitle' 		=> $post->getMetaTitle(),
			'metaKeywords' 		=> $post->getMetaKeywords(),
			'metaDescription' 	=> $post->getMetaDescription(),
		));

		// Prerender the sections, as referencing twig within itself causes a circular reference
		$sections = $this->get('stems.core.sections.manager')->setBundle('blog')->renderSections($post);

		return array(
			'post'     => $post,
			'page'     => $this->page,
			'sections' => $sections
		);
	}

	/**
	 * Display an instant outfit post
	 *
	 * @Route("/instant-outfit/{slug}", name="thread_editorspicks_front_instant_outfit")
	 * @Template()
	 */
	public function instantOutfitAction(Post $post)
	{
		// Redirect to the index if the collection isn't published
		if ($post->getStatus() !== 'Published' || $post->getCategory()->getSlug() !== 'instant-outfit') {
			$this->redirect($this->generateUrl('thread_editorspicks_front_list'));
		}

		$this->loadPage('instant-outfit/{slug}', array(
			'title' 			=> $post,
			'windowTitle' 		=> $post->getMetaTitle(),
			'metaKeywords' 		=> $post->getMetaKeywords(),
			'metaDescription' 	=> $post->getMetaDescription(),
		));

		return array(
			'post' => $post,
			'page' => $this->page,
		);
	}
}
