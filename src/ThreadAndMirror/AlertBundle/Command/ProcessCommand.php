<?php

namespace ThreadAndMirror\AlertBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends ContainerAwareCommand
{
	protected $em;

	protected $mailer;

	protected $twig;

	protected function configure()
	{
		$this
			->setName('alert:process')
			->setDescription('Process alerts of a specific type.')
			->addArgument('type', InputArgument::OPTIONAL, 'The type of alert to process')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// load the necessary services
		$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$this->mailer = $this->getContainer()->get('mailer');
		$this->twig = $this->getContainer()->get('templating');

		// build the method name for the parser
		$method = 'process'.$input->getArgument('type').'Alerts';

		// run the relevant alert processor
		$alerts = $this->$method();

		// update the db
		$this->em->flush();

		// notify the amount of alerts processed
		$output->writeln($alerts.' alerts processed.');
	}

	protected function processBackInStockAlerts()
	{
		// get the back-in-stock alerts that haven't been processed yet
		$alerts = $this->em->getRepository('ThreadAndMirrorAlertBundle:AlertBackInStock')->findBy(array('processed' => null), array('added' => 'ASC'), 1000);

		// process each alert
		foreach ($alerts as $alert) {
			
			// get the product associated with the alert
			$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($alert->getProduct());

			// skip if the product no longer exists (hard deleted)
			if (!$product) {
				$alert->setProcessed(new \DateTime());
				$this->em->persist($alert);
				continue;
			}

			// cycle through each user pick that is watching the product and send an e-mail alert
			foreach ($product->getPicks() as $pick) {
				
				// only handle the pick if it's attached to a Wishlist
				if ($pick->getWishlist()) {

					// get the user who owns the pick
					$user = $this->em->getRepository('StemsUserBundle:User')->find($pick->getWishlist()->getOwner());

					// ignore any wishlists without owners
					if (is_object($user)) {

						// send the alert
						$message = \Swift_Message::newInstance()
							->setSubject('Back In Stock: '.$product->getName())
							->setFrom(array('notify@threadandmirror.com' => 'Thread & Mirror'))
							->setTo(array($user->getEmail() => $user->getFullname()))
							->setContentType('text/html')
							->setBody(
								$this->twig->render(
									'ThreadAndMirrorAlertBundle:Email:alertBackInStock.html.twig',
									array('product' => $product)
								)
							)
						;
						$this->mailer->send($message);

						$alert->addNotification();
					}

				}
			}

			// set the alert as processed
			$alert->setProcessed(new \DateTime());
			$this->em->persist($alert);
		}

		return count($alerts);
	}

	protected function processNowOnSaleAlerts()
	{

		// get the back-in-stock alerts that haven't been processed yet, only handling the 1000 oldest to prevent memory overload
		$alerts = $this->em->getRepository('ThreadAndMirrorAlertBundle:AlertNowOnSale')->findBy(array('processed' => null), array('added' => 'ASC'), 1000);

		// process each alert
		foreach ($alerts as $alert) {
			
			// get the product associated with the alert
			$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($alert->getProduct());

			// skip if the product no longer exists (hard deleted)
			if (!$product) {
				$alert->setProcessed(new \DateTime());
				$this->em->persist($alert);
				continue;
			}

			// cycle through each user pick that is watching the product and send an e-mail alert
			foreach ($product->getPicks() as $pick) {
				
				// only handle the pick if it's attached to a Wishlist
				if ($pick->getWishlist()) {

					// get the user who owns the pick
					$user = $this->em->getRepository('StemsUserBundle:User')->find($pick->getWishlist()->getOwner());

					// ignore any wishlists without owners
					if (is_object($user)) {

						// send the alert
						$message = \Swift_Message::newInstance()
							->setSubject('Now On Sale: '.$product->getName())
							->setFrom(array('notify@threadandmirror.com' => 'Thread & Mirror'))
							->setTo(array($user->getEmail() => $user->getFullname()))
							->setContentType('text/html')
							->setBody(
								$this->twig->render(
									'ThreadAndMirrorAlertBundle:Email:alertNowOnSale.html.twig',
									array('product' => $product)
								)
							)
						;
						$this->mailer->send($message);

						$alert->addNotification();
					}
				}
			}

			// set the alert as processed
			$alert->setProcessed(new \DateTime());
			$this->em->persist($alert);
		}

		return count($alerts);
	}

	protected function processFurtherPriceChangeAlerts()
	{

		// get the back-in-stock alerts that haven't been processed yet
		$alerts = $this->em->getRepository('ThreadAndMirrorAlertBundle:AlertFurtherPriceChange')->findBy(array('processed' => null), array('added' => 'ASC'), 1000);

		// process each alert
		foreach ($alerts as $alert) {
			
			// get the product associated with the alert
			$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($alert->getProduct());

			// skip if the product no longer exists (hard deleted)
			if (!$product) {
				$alert->setProcessed(new \DateTime());
				$this->em->persist($alert);
				continue;
			}

			// cycle through each user pick that is watching the product and send an e-mail alert
			foreach ($product->getPicks() as $pick) {
				
				// only handle the pick if it's attached to a Wishlist
				if ($pick->getWishlist()) {

					// get the user who owns the pick
					$user = $this->em->getRepository('StemsUserBundle:User')->find($pick->getWishlist()->getOwner());

					// ignore any wishlists without owners
					if (is_object($user)) {

						// send the alert
						$message = \Swift_Message::newInstance()
							->setSubject('Price Reduced Further: '.$product->getName())
							->setFrom(array('notify@threadandmirror.com' => 'Thread & Mirror'))
							->setTo(array($user->getEmail() => $user->getFullname()))
							->setContentType('text/html')
							->setBody(
								$this->twig->render(
									'ThreadAndMirrorAlertBundle:Email:alertFurtherPriceChange.html.twig',
									array('product' => $product)
								)
							)
						;
						$this->mailer->send($message);

						$alert->addNotification();
					}
				}
			}

			// set the alert as processed
			$alert->setProcessed(new \DateTime());
			$this->em->persist($alert);
		}

		return count($alerts);
	}

	protected function processSizeInStockAlerts()
	{
		// get the back-in-stock alerts that haven't been processed yet
		$alerts = $this->em->getRepository('ThreadAndMirrorAlertBundle:AlertSizeInStock')->findBy(array('processed' => null), array('added' => 'ASC'), 1000);

		// process each alert
		foreach ($alerts as $alert) {
			
			// get the product associated with the alert
			$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($alert->getProduct());

			// skip if the product no longer exists (hard deleted)
			if (!$product) {
				$alert->setProcessed(new \DateTime());
				$this->em->persist($alert);
				continue;
			}

			// cycle through each user pick that is watching the product and send an e-mail alert
			foreach ($product->getPicks() as $pick) {
				
				// only handle the pick if it's attached to a Wishlist
				if ($pick->getWishlist()) {

					// check if the pick matches the size of the alert
					if (in_array($alert->getSize(), $pick->getSizes())) {

						// if there are any general back-in-stock alerts queue up for the same product, mark as processed too
						$backInStockAlerts = $this->em->getRepository('ThreadAndMirrorAlertBundle:AlertBackInStock')->findBy(array('processed' => false, 'product' => $alert->getProduct()));

						foreach ($backInStockAlerts as $backInStockAlert) {
							$backInStockAlert->setProcessed(new \DateTime());
							$this->em->persist($backInStockAlert);
						}

						// get the user who owns the pick
						$user = $this->em->getRepository('StemsUserBundle:User')->find($pick->getWishlist()->getOwner());

						// ignore any wishlists without owners
						if (is_object($user)) {

							// sent the alert
							$message = \Swift_Message::newInstance()
								->setSubject('Your Size is Back in Stock: '.$product->getName())
								->setFrom(array('notify@threadandmirror.com' => 'Thread & Mirror'))
								->setTo(array($user->getEmail() => $user->getFullname()))
								->setContentType('text/html')
								->setBody(
									$this->twig->render(
										'ThreadAndMirrorAlertBundle:Email:alertBackInStock.html.twig',
										array('product' => $product)
									)
								)
							;
							$this->mailer->send($message);

							$alert->addNotification();
						}
					}
				}
			}

			// set the alert as processed
			$alert->setProcessed(new \DateTime());
			$this->em->persist($alert);
		}

		return count($alerts);
	}
}