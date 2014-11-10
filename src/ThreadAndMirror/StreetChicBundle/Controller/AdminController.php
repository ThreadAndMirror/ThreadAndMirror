<?php

namespace ThreadAndMirror\StreetChicBundle\Controller;

// Dependencies
use Stems\CoreBundle\Controller\BaseAdminController,
	Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class AdminController extends BaseAdminController
{
	protected $home = '';

	/**
	 * Social dashboard
	 */
	public function indexAction()
	{		
		return $this->render('ThreadAndMirrorStreetChicBundle:Admin:index.html.twig', array());
	}
}
