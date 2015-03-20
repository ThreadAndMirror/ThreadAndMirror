<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ThreadAndMirror\EditorsPicksBundle\Form\CollectionType;
use ThreadAndMirror\EditorsPicksBundle\Entity\Pick;
use ThreadAndMirror\EditorsPicksBundle\Entity\Collection;
use Doctrine\ORM\NoResultException;

/**
 * @Route("/admin/editors-picks")
 */
class AdminController extends BaseAdminController
{
	protected $homePath = 'thread_editorspicks_admin_collections';
	
	/**
	 * List editor's picks collections
	 *
	 * @Route("/", name="thread_editorspicks_admin_collections")
	 * @Template()
	 */
	public function indexAction()
	{		
		// Get all undeleted collections
		$collections = $this->em->getRepository('ThreadAndMirrorEditorsPicksBundle:Collection')->findBy(array('deleted' => false), array('created' => 'DESC'));

		return array(
			'collections' => $collections,
		);
	}

	/**
	 * Create a new collection
	 *
	 * @Route("/create", name="thread_editorspicks_admin_collections_create")
	 * @Template()
	 */
	public function createAction(Request $request)
	{
		$response = $this->forward('ThreadAndMirrorEditorsPicksBundle:Admin:edit', array(
			'request'    => $request,
			'collection' => new Collection()
		));

		return $response;
	}

	/**
	 * Edit a collection
	 *
	 * @Route("/edit/{id}", name="thread_editorspicks_admin_collections_edit")
	 * @Template()
	 */
	public function editAction(Request $request, Collection $collection)
	{
		// Create the edit form and forms for the sections
		$form = $this->createForm(new CollectionType(), $collection);

		// Handle the form submission
		if ($request->getMethod() == 'POST') {

			// Validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {

				// Update the post in the database
				$collection->setOwner($this->getUser()->getId());

				// Save the order of the products
				$position = 1;

				foreach ($collection->getPicks() as $pick) {
					$pick->setPosition($position);
					$position++;
				}
					
				$this->em->persist($collection);
				$this->em->flush();

				$request->getSession()->getFlashBag()->set('success', 'The collection "'.$collection->getHeader().'" has been saved.');

				return $this->redirect($this->generateUrl($this->homePath));

			} else {
				$message = 'Your request was not processed as errors were found: ';

				foreach ($form->getErrors() as $error) {
					$message .= '<br><br> &bull; '.$error->getMessage();
				}

				$request->getSession()->getFlashBag()->set('error', $message);
			}
		}

		return array(
			'form'			=> $form->createView(),
			'collection' 	=> $collection,
		);
	}

	/**
	 * Delete a collection
	 *
	 * @Route("/delete/{id}", name="thread_editorspicks_admin_collections_delete")
	 */
	public function deleteAction(Request $request, Collection $collection)
	{
		// Delete the collection if was found
		$collection->setDeleted(true);
		$this->em->persist($post);
		$this->em->flush();

		// Return the success message
		$request->getSession()->getFlashBag()->set('success', 'The collection "'.$collection->getTitle().'" was successfully deleted!');

		return $this->redirect($this->generateUrl($this->homePath));
	}

	/**
	 * Publish or unpublish a collection
	 *
	 * @Route("/publish/{id}", name="thread_editorspicks_admin_collections_publish")
	 */
	public function publishAction(Request $request, Collection $collection)
	{
		// Set the post to published/unpublished 
		if ($collection->getStatus() == 'Draft') {	
			$collection->setStatus('Published');
			$collection->setPublished(new \DateTime());
			$request->getSession()->getFlashBag()->set('success', 'The collection "'.$collection->getHeader().'" was successfully published!');
		} else {
			$collection->setStatus('Draft');
			$request->getSession()->getFlashBag()->set('success', 'The collection "'.$collection->getHeader().'" was successfully unpublished!');
		}

		$this->em->persist($collection);
		$this->em->flush();

		return $this->redirect($this->generateUrl($this->homePath));
	}
}
