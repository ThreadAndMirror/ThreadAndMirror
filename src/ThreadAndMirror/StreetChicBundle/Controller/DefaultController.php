<?php

namespace ThreadAndMirror\StreetChicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ThreadAndMirrorStreetChicBundle:Default:index.html.twig', array('name' => $name));
    }
}
