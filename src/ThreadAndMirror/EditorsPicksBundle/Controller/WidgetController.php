<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

// Symfony Components
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContext,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class WidgetController extends Controller
{
	/**
	 * Renders a block with the latest editors pick collection that's a single item
	 */
	public function latestFeaturedPickAction()
	{
		// get the latest editors pick
		$em = $this->getDoctrine()->getEntityManager();
		$collection = $em->getRepository('ThreadAndMirrorEditorsPicksBundle:Collection')->findBy(array('deleted' => false, 'status' => 'Published', 'status' => 'single'), array('created' => 'DESC'), 1);

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:editorsPicks.html.twig', array(
			'collection' 	=> reset($collection)
		));
	}

	/**
	 * Renders a block with the latest collection
	 */
	public function editorsPicksAction()
	{
		// get the latest editors pick
		$em = $this->getDoctrine()->getEntityManager();
		$collection = $em->getRepository('ThreadAndMirrorEditorsPicksBundle:Collection')->findBy(array('deleted' => false, 'status' => 'Published'), array('created' => 'DESC'), 1);

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:editorsPicks.html.twig', array(
			'collection' 	=> reset($collection)
		));
	}
}
