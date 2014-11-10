<?php

namespace ThreadAndMirror\AlertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\HttpFoundation\Response;


class TaskController extends Controller
{
    /**
     *  Processes all outstanding alerts of a specific type
     */
    public function processAlertsAction($type)
    {
        $em = $this->getDoctrine()->getManager();

        // build the method name for the parser
		$method = 'process'.ucfirst(str_replace('-', '', $shop)).'Alerts';

		// run the relevant alert processor
		$alerts = $this->$method($em);

        // update the db
        $em->flush();

        // notify the amount of alerts processed
        return new Response($alerts.' alerts processed.');
    }

    /**
     * Converts all processed alerts into their archive version and updates the db
     */
    public function archiveAlertsAction()
    {
        // get the back-in-stock alerts that have been processed
        // $alerts = $em->getRepository('ThreadAndMirrorAlertBundle:AlertBackInStock')->findBy('processed' => null);
    }
}
