<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContext,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class WidgetController extends Controller
{
	/**
	 * Render the homepage grid widget containing latest posts
	 *
	 * @param  integer 	$limit  	Amount of posts to render
	 */
	public function homepageFeatureAction($limit=24)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), $limit);

		return $this->render('ThreadAndMirrorSocialBundle:Widget:homepageFeature.html.twig', array(
			'posts' 		=> $posts,
		));
	}

	/**
	 * Slider widget containing the n latest posts
	 *
	 * @param  integer 	$limit  	Amount of posts to render
	 */
	public function latestPostsAction($limit=8)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), $limit);

		return $this->render('ThreadAndMirrorSocialBundle:Widget:latestPosts.html.twig', array(
			'posts' 		=> $posts,
		));
	}
}
