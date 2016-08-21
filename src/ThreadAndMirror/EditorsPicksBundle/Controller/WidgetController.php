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
use ThreadAndMirror\BlogBundle\Entity\Category;
use ThreadAndMirror\BlogBundle\Entity\Post;

class WidgetController extends Controller
{
	/**
	 * Renders a block with the latest editor's picks collection
	 * @Template()
	 */
	public function editorsPicksAction()
	{
		// Get the latest editors pick article and it's products
		$em = $this->getDoctrine()->getManager();
		$post = $this->get('threadandmirror.blog.service.post')->getLatestPublishedPostForCategory(Category::EDITORS_PICKS);

		$products = [];

		foreach($post->getSections() as $section) {
			if ($section->getType() == 'product') {
				$products[] = $em->getRepository('ThreadAndMirrorProductsBundle:SectionProduct')->find($section->getEntity());
				if (count($products) == 4) {
					break;
				}
			}
		}

		return $this->render('ThreadAndMirrorEditorsPicksBundle:Widget:editorsPicks.html.twig', [
			'post' 	   => $post,
			'products' => $products
		]);
	}
}
