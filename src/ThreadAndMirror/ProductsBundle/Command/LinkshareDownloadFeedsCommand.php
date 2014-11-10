<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface,
	Symfony\Bridge\Monolog\Logger;


class LinkshareDownloadFeedsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('linkshare:downloadFeeds')
			->setDescription('Downloads data feeds for all affiliates.')
			->addArgument('type', InputArgument::REQUIRED, 'The feed file to use, either \'full\' or \'delta\'.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ready the logger
		$logger = new Logger('threadandmirror');

		// get the ftp and em service
		$ftp = $this->getContainer()->get('ijanki_ftp');
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

		// only get the shops that are on the linkshare program
		$shops = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('linkshare' => true, 'hasFeed' => true));

		// connect to linkshare in passive mode
		$ftp->connect('aftp.linksynergy.com');
		$ftp->login('triciamuldoo', '3ZsRf3yX');
		$ftp->pasv(true);

		// download the gzip files for each shop
		$filenames = array();
		$site      = '3146542';
		$suffix    = $input->getArgument('type') == 'delta' ? '_delta' : '';
		$saveDir   = $this->getContainer()->get('kernel')->getRootDir().'/../web/xml/datafeeds/';

		foreach ($shops as $shop) {
			
			// construct the filename template based on the files we want to download
			$filename = $shop->getAffiliateId().'_'.$site.'_mp'.$suffix.'.xml.gz';

			try
			{
				// check the last time the remote binary was changed
				$modified = $ftp->mdtm($filename.'.lmp');
				$modified = new \DateTime(date('Y-m-d h:i:s', $modified));

				// get the file if it's newer than what we have or if we're doing a full feed download
				if (!$shop->getFeedModified() || $modified > $shop->getFeedModified() || $input->getArgument('type') == 'full') {

					$ftp->get($saveDir.$filename, $filename, FTP_BINARY);
					$filenames[] = $saveDir.$filename;

					// save the date of the new file
					$shop->setFeedModified($modified);
					$em->persist($shop);

					// echo for command line debugging
					echo 'New feed downloaded for '.$shop->getName().PHP_EOL;
				} else {
					echo 'Remote feed is older for  '.$shop->getName().PHP_EOL;
				}			
			}
			catch (\Exception $e)
			{
				$logger->error('No '.$input->getArgument('type').' datafeed files available for '.$shop->getName().' ('.$shop->getAffiliateId().')');
			}
 		}  

		// unzip the downloaded .gz files
		foreach ($filenames as $filename) {

			$buffer = 128000; // read 128kb at a time
			$unzipped = str_replace('.gz', '', $filename);

			// open our files (in binary mode)
			$gz = gzopen($filename, 'rb');
			$output = fopen($unzipped, 'wb');

			// keep repeating until the end of the input file
			while (!gzeof($gz)) {
			    // both fwrite and gzread and binary-safe
			    fwrite($output, gzread($gz, $buffer));
			}

			fclose($output);
			gzclose($gz);
		}

		$em->flush();
	}
}