<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThreadAndMirror\ProductsBundle\Service\Affiliate\AffiliateWindowService;
use ThreadAndMirror\ProductsBundle\Service\Affiliate\LinkshareService;

class ProcessProductFeedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('affiliate:products:process_feeds')
            ->setDescription('Queue processing of feeds files for an affiliate')
            ->addArgument('affiliate', InputArgument::REQUIRED, 'The affiliate to process feeds for.')
	        ->addOption('area', 'a', InputOption::VALUE_OPTIONAL,'Only process files for a specific area. (affiliate_window only)', null)
	        ->addOption('category', 'c', InputOption::VALUE_OPTIONAL,'Only process files for a specific affiliate category ID. (affiliate_window only)', null)
	        ->addOption('merchantId', 'm', InputOption::VALUE_OPTIONAL,'Only process files for a specific merchant ID. (linkshare only)', null);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load the affiliate service
        $affiliateService = $this->getContainer()->get('threadandmirror.affiliate.'.$input->getArgument('affiliate'));

	    // Process the feed files
	    switch ($input->getArgument('affiliate'))
	    {
		    case AffiliateWindowService::KEY_NAME:
			    $affiliateService->queueFeedFileProcessing($input->getOption('area'), $input->getOption('category'));
			    break;

		    case LinkshareService::KEY_NAME:
			    $affiliateService->queueFeedFileProcessing($input->getOption('merchantId'));
			    break;
	    }
    }
}