<?php

namespace ThreadAndMirror\MoodBoardBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ThreadAndMirror\MoodBoardBundle\Form\MoodBoardType;
use ThreadAndMirror\MoodBoardBundle\Entity\MoodBoard;

/**
 * @Route("/admin/moodboards")
 */
class AdminController extends BaseAdminController
{
	protected $homePath = 'thread_moodboard_admin_moodboards';
	
	/**
	 * List editor's picks moodboards
	 *
	 * @Route("/", name="thread_moodboard_admin_moodboards")
	 * @Template()
	 */
	public function indexAction()
	{		
		// Get all undeleted moodboards
		$moodboards = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Moodboard')->findBy(array('deleted' => false), array('created' => 'DESC'));

		return array(
			'moodboards' => $moodboards,
		);
	}

	/**
	 * Create a new moodboard
	 *
	 * @Route("/create", name="thread_moodboard_admin_moodboards_create")
	 * @Template()
	 */
	public function createAction(Request $request)
	{
		$response = $this->forward('ThreadAndMirrorMoodBoardBundle:Admin:edit', array(
			'request'    => $request,
			'moodboard'  => new MoodBoard()
		));

		return $response;
	}

	/**
	 * Edit a moodboard
	 *
	 * @Route("/edit/{id}", name="thread_MoodBoard_admin_moodboards_edit")
	 * @Template()
	 */
	public function editAction(Request $request, MoodBoard $moodboard)
	{
		// Create the edit form and forms for the sections
		$form = $this->createForm(new MoodBoardType(), $moodboard);

		// Handle the form submission
		if ($request->getMethod() == 'POST') {

			// Validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {


				// Save the order of the products
				$position = 1;

				foreach ($moodboard->getElements() as $element) {
					$element->setPosition($position);
					$position++;
				}
					
				$this->em->persist($moodboard);
				$this->em->flush();

				$request->getSession()->getFlashBag()->set('success', 'The moodboard "'.$moodboard->getTitle().'" has been saved.');

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
			'moodboard' 	=> $moodboard,
		);
	}
}