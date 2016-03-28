<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use ThreadAndMirror\BlogBundle\Entity\Post;
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
