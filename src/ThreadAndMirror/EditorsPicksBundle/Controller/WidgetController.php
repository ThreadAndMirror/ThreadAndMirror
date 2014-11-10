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
	 * Renders a block with the latest editors pick
	 */
	public function latestEditorsPickAction()
	{
		// get the latest editors pick
		$em = $this->getDoctrine()->getEntityManager();
		$picks = $em->getRepository('ThreadAndMirrorEditorsPicksBundle:Pick')->findBy(array('deleted' => false), array('added' => 'DESC'), 1);
		$pick = reset($picks);

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:latestEditorsPick.html.twig', array(
			'pick' 	=> $pick,
		));
	}

	/**
	 * Renders a block with the latest pick as a feature and four other picks
	 */
	public function editorsPicksAction()
	{
		// get the latest editors pick
		$em = $this->getDoctrine()->getEntityManager();
		$picks = $em->getRepository('ThreadAndMirrorEditorsPicksBundle:Pick')->findBy(array('deleted' => false), array('added' => 'DESC'), 5);

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:editorsPicks.html.twig', array(
			'picks' 	=> $picks,
		));
	}
}
