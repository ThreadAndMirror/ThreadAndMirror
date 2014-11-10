<?php

namespace ThreadAndMirror\SocialBundle\Twig;

use Doctrine\ORM\EntityManager;

class SocialExtension extends \Twig_Extension
{
	/**
	 * The entity manager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('isFavouriteFeed', array($this, 'isFavouriteFeed')),
		);
	}

	public function getName()
	{
		return 'threadandmirror_social_extension';
	}

	/**
	 * Checks if the feed in question is a favourite of the current user
	 *
	 * @param  integer  $id         The id if the feed
	 * @return bool               	Whether the feed is a favourite
	 */
	public function isFavouriteFeed($id, $user)
	{
		// Check the user is logged in fully
		if ($user && in_array('ROLE_USER', $user->getRoles())) {

			// Get the user's social profile
			$profile = $this->em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($user->getId());

			// See if the feed is in their favourite list
			if (in_array($id, $profile->getSocialFeeds())) {
				return true;
			}
		}

		return false;
	}	
}