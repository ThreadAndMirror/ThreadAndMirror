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

class WidgetController extends Controller
{
	/**
	 * Renders a block with the latest editor's picks collection
	 * @Template()
	 */
	public function editorsPicksAction()
	{
		// get the latest editors pick
		$em = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('StemsBlogBundle:Post')->findPublishedPostsByCategory('editors-picks', 1);

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:editorsPicks.html.twig', array(
			'post' 	=> reset($posts)
		));
	}
}
