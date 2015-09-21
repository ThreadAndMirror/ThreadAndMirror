<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadProductFeedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('affiliate:products:download_feeds')
            ->setDescription('Download feeds files for an affiliate')
            ->addArgument('affiliate', InputArgument::REQUIRED, 'The affiliate to download feeds for.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load the affiliate service
        $affiliateService = $this->getContainer()->get('threadandmirror.affiliate.'.$input->getArgument('affiliate'));

        // Download the feed files
        $affiliateService->queueFeedFileDownloads();
    }
}