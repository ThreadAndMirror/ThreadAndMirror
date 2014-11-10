<?php

namespace ThreadAndMirror\AlertBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	ThreadAndMirror\AlertBundle\Entity\ArchiveBackInStock,
	ThreadAndMirror\AlertBundle\Entity\ArchiveNowOnSale,
	ThreadAndMirror\AlertBundle\Entity\ArchiveSizeInStock,
	ThreadAndMirror\AlertBundle\Entity\ArchiveFurtherPriceChange;

class ArchiveCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('alert:archive')
			->setDescription('Archives all alerts that have been processed.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// load the necessary services
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');        

		// get each type of alert, create an archive copy and remove the original
		$count = 0;

		// back in stock
		$alerts = $em->getRepository('ThreadAndMirrorAlertBundle:AlertBackInStock')->getProcessed();

		foreach ($alerts as &$alert) {
			$archive = new ArchiveBackInStock($alert);
			$em->persist($archive);
			$em->remove($alert);
			$count++;

			// flush every 100 alerts to avoid them building up in memory
			$count % 100 === 0 and $em->flush();
		}

		$output->writeln('Back in stock archived.');

		// now on sale
		$alerts = $em->getRepository('ThreadAndMirrorAlertBundle:AlertNowOnSale')->getProcessed();

		foreach ($alerts as &$alert) {
			$archive = new ArchiveNowOnSale($alert);
			$em->persist($archive);
			$em->remove($alert);
			$count++;

			// flush every 100 alerts to avoid them building up in memory
			$count % 100 === 0 and $em->flush();
		}

		$output->writeln('Now on sale archived.');

		// size in stock
		$alerts = $em->getRepository('ThreadAndMirrorAlertBundle:AlertSizeInStock')->getProcessed();

		foreach ($alerts as &$alert) {
			$archive = new ArchiveSizeInStock($alert);
			$em->persist($archive);
			$em->remove($alert);
			$count++;

			// flush every 100 alerts to avoid them building up in memory
			$count % 100 === 0 and $em->flush();
		}

		$output->writeln('Size in stock archived.');

		// further price change
		$alerts = $em->getRepository('ThreadAndMirrorAlertBundle:AlertFurtherPriceChange')->getProcessed();

		foreach ($alerts as &$alert) {
			$archive = new ArchiveFurtherPriceChange($alert);
			$em->persist($archive);
			$em->remove($alert);
			$count++;

			// flush every 100 alerts to avoid them building up in memory
			$count % 100 === 0 and $em->flush();
		}

		$output->writeln('Further price change archived.');

		// flush the remainder
		$em->flush();

		// notify the amount of alerts processed
		$output->writeln($count.' alerts archived.');
	}
}