<?php

namespace ThreadAndMirror\InstaInspoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ThreadAndMirror\InstaInspoBundle\Entity\Post;

class FrontController extends Controller
{
    /**
     * Display an insta inspo post
     *
     * @Route("/insta-inspo/{pid}", name="thread_instainspo_front_post")
     * @Template()
     */
    public function postAction(Post $post)
    {


        return new Response();


    }
}
