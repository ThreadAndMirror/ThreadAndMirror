<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\EditorsPicksBundle\Entity\Collection;
use ThreadAndMirror\EditorsPicksBundle\Entity\Pick;
use ThreadAndMirror\EditorsPicksBundle\Form\CollectionType;

class RestController extends BaseRestController
{
	/**
	 * Validate a pick form
	 *
	 * @Route("/admin/rest/editors-picks/validate/pick/{offset}", name="thread_editorspicks_rest_validate_pick")
	 */
	public function validatePick(Request $request, $offset)
	{
		// Create a dummy collection so we can validate the child pick
		$collection = new Collection();

		// Turn off validation on the collection, including the CSRF token
		$form = $this->createForm(new CollectionType(true), $collection, array(
			'validation_groups' => array('picks_only'),
			'csrf_protection'   => false,
		));

		// Handle the form submission
		if ($request->getMethod() == 'POST') {

			// Validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {

				// Get the html for the pick editor form
				$html = $this->renderView('ThreadAndMirrorEditorsPicksBundle:Rest:pick.html.twig', array(
					'form'		=> $form->createView(),
					'offset'	=> $offset
				));

				return $this->addHtml($html)->setCallback('addEditorsPickCallback')->success()->sendResponse();

			} else {
				// Add the validation errors to the response
				$this->addValidationErrors($form);
				
				return $this->error('There was an error submitting your form.')->sendResponse();
			}
		}
	}
}
