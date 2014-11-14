<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController,
	Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request,
	ThreadAndMirror\SocialBundle\Entity\Feed,
	ThreadAndMirror\SocialBundle\Entity\Post;

class AdminController extends BaseAdminController
{
	protected $home = 'thread_admin_social_overview';

	/**
	 * Social overview page with feed owners listed
	 */
	public function indexAction()
	{		
		// get all feed owners
		$em     = $this->getDoctrine()->getEntityManager();
		$owners = $em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findAll();

		return $this->render('ThreadAndMirrorSocialBundle:Admin:index.html.twig', array(
			'owners' 	=> $owners,
		));
	}

	/**
	 * Update all feeds of the specified type
	 */
	public function updateFeedsAction(Request $request, $type)
	{
		// run the update service and store the error count, if any
		$errors = $this->get('threadandmirror.social.feeds')->updateFeeds($type);

		// set the completion message and redirect to the social overview
		if ($errors) {
			$request->getSession()->getFlashBag()->set('warning', 'Some '.ucfirst($type).' feeds were updated, but there were '.$errors.' failures');
		} else {
			$request->getSession()->getFlashBag()->set('success', 'All '.ucfirst($type).' feeds were updated');
		}
		
		return $this->redirect($this->generateUrl($this->home));
	}
}