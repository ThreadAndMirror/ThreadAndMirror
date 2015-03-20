<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stems\CoreBundle\Controller\BaseFrontController;
use ThreadAndMirror\EditorsPicksBundle\Entity\Collection;

class FrontController extends BaseFrontController
{
	/**
	 * Display an editor's picks collection
	 *
	 * @Route("/editors-picks", name="thread_editorspicks_front_collections")
	 * @Template()
	 */
	public function listAction()
	{
		// Load the page object from the CMS
		$this->loadPage('editors-picks/{slug}', array(
			'title' 			=> $collection,
			'windowTitle' 		=> $collection->getMetaTitle(),
			'metaKeywords' 		=> $collection->getMetaKeywords(),
			'metaDescription' 	=> $collection->getMetaDescription(),
		));

		return array(
			'collections' => $collections,
			'page'	      => $this->page,
		);
	}

	/**
	 * Preview a collection that isn't published yet
	 *
	 * @Route("/editors-picks/preview/{slug}", name="thread_editorspicks_front_collection_preview")
	 * @Security("has_role('ROLE_ADMIN')")
	 * @Template()
	 */
	public function previewAction(Collection $collection)
	{
		// Load the page object from the CMS
		$this->loadPage('editors-picks/{slug}', array(
			'title' 			=> $collection,
			'windowTitle' 		=> $collection->getMetaTitle(),
			'metaKeywords' 		=> $collection->getMetaKeywords(),
			'metaDescription' 	=> $collection->getMetaDescription(),
		));

		return array(
			'collection' => $collection,
			'page'	     => $this->page,
		);
	}

	/**
	 * Display an editor's picks collection
	 *
	 * @Route("/editors-picks/{slug}", name="thread_editorspicks_front_collection")
	 * @Template()
	 */
	public function collectionAction(Collection $collection)
	{
		// Redirect to the index if the collection isn't published
		if ($collection->getStatus() !== 'Published') {
			$this->redirect($this->generateUrl('thread_editorspicks_front_collections'));
		}

		// Load the page object from the CMS
		$this->loadPage('editors-picks/{slug}', array(
			'title' 			=> $collection,
			'windowTitle' 		=> $collection->getMetaTitle(),
			'metaKeywords' 		=> $collection->getMetaKeywords(),
			'metaDescription' 	=> $collection->getMetaDescription(),
		));

		return array(
			'collection' => $collection,
			'page'	     => $this->page,
		);
	}
}
