<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\EditorsPicksBundle\Form\PickType;
use ThreadAndMirror\EditorsPicksBundle\Entity\Pick;
use Doctrine\ORM\NoResultException;

class AdminController extends BaseAdminController
{
	/**
	 * Create a new pick
	 */
	public function createAction(Request $request)
	{
		$em = $this->getDoctrine()->getEntityManager();

		// build the form object (for submissions only)
		$pick = new Pick();
		$form = $this->createForm(new PickType(), $pick); 


		// handle the form submission
		if ($request->getMethod() == 'POST') {

			// validate the submitted values
			$form->bindRequest($request);

			if ($form->isValid()) {

				// update the new pick
				$pick->setAuthor($this->getUser()->getId());
				$em->persist($pick);
				$em->flush();

				$request->getSession()->setFlash('success', 'The editor\'s pick "'.$pick->getName().'" has been added.');
			} else {
				// display the error messages
				$message = 'There was a error handling your submission:<br><br>';
				foreach ($form->getErrors() as $error) {
					$message .= $error.'<br>';
				}
				$request->getSession()->setFlash('error', $message);
			}
		}

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Admin:create.html.twig', array(
			'form'		=> $form->createView(),
			'pick'		=> $pick,
			'method'	=> $request->getMethod(),
		));
	}
}
