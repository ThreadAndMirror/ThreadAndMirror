<?php

namespace ThreadAndMirror\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stems\CoreBundle\Controller\BaseFrontController;
use Stems\PageBundle\Annotation\PageAnnotation as StemsPage;

class FrontController extends BaseFrontController
{
	/**
	 * Home page
	 *
	 * @Route("/", name="thread_core_home", options={"sitemap" = {"changefreq" = "daily" }})
	 * @Template()
	 */
	public function indexAction()
	{
		return [];
	}

	/**
	 * Embeds the contextual main menu on the site
	 *
	 * @param string    $route      The route name of the main request
	 *
	 * @Template()
	 */
	public function menuAction($route)
	{
		return [
			'route' => $route
		];
	}

	/**
	 * About us page
	 *
	 * @Route("/about", name="thread_core_about", options={"sitemap" = {"changefreq" = "yearly" }})
	 * @Template()
	 */
	public function aboutAction()
	{
		return [];
	}

	/**
	 * Privacy policy page
	 *
	 * @Route("/privacy-policy", name="thread_core_privacy_policy", options={"sitemap" = {"changefreq" = "yearly" }})
	 * @Template()
	 */
	public function privacyAction()
	{
		return [];
	}

	/**
	 * Contact us page
	 *
	 * @Route("/contact", name="thread_core_contact", options={"sitemap" = {"changefreq" = "yearly" }})
	 * @Template()
	 */
	public function contactAction()
	{
		return [];
	}
}
