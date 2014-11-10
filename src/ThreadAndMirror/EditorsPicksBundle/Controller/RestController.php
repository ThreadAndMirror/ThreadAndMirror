<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\JsonResponse,
	Symfony\Component\HttpFoundation\Request;

use ThreadAndMirror\EditorsPicksBundle\Entity\Pick;

use ThreadAndMirror\EditorsPicksBundle\Form\PickType;

class RestController extends Controller
{
	/**
	 * Generate the pick form based on whether a url or pid was passed
	 */
	public function generatePickAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		// try to find the product based on the passed param
		if ($request->get('pid')) {
			$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($request->get('pid'));
		} else {
			$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getProductFromUrl($request->get('url'));
		}

		// if we manage to find a product then build a new pick form
		if (is_object($product)) {

			// create the pick and form
			$pick = new Pick($product);
			$form = $this->createForm(new PickType(), $pick);

			// get the html for the pick editor form
			$html = $this->renderView('ThreadAndMirrorEditorsPicksBundle:Rest:form.html.twig', array(
				'form'		=> $form->createView(),
				'pick'		=> $pick,
			));

			// success response
			return new JsonResponse(array(
				'html'		=> $html,
				'success'   => true,
				'message' 	=> 'The pick form has been created.'
			));
		} else {
			// error response
			return new JsonResponse(array(
				'success'   => false,
				'message' 	=> 'We could not load a product using that link or product ID.'
			));
		}
	}
}
