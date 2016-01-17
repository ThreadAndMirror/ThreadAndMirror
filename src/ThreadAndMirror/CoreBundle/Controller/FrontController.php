<?php

namespace ThreadAndMirror\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stems\CoreBundle\Controller\BaseFrontController;
use Stems\PageBundle\Entity\Page;
use Stems\PageBundle\Annotation\PageAnnotation as StemsPage;

class FrontController extends BaseFrontController
{
	/**
	 * Embeds the contextual main menu on the site
	 *
	 * @Template()
	 */
	public function menuAction($slug)
	{
		return [
			'slug' => $slug
		];
	}

	/**
	 * About page
	 *
	 * @Route("/about", name="thread_core_about")
	 * @StemsPage(layout="banner", title="About Thread & Mirror", windowTitle="About")
	 * @Template()
	 *
	 */
	public function aboutAction()
	{
		return [
			'page' => $this->page
		];
	}
}
