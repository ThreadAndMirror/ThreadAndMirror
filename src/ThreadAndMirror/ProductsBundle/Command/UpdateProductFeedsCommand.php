<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProductFeedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('affiliate:products:update_feeds')
            ->setDescription('Updates product listing using feeds files for an affiliate')
            ->addArgument('slug', InputArgument::REQUIRED, 'The shop to update products for.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
         // Get the shop
        $em      = $this->getContainer()->get('doctrine.orm.entity_manager');
        $shop    = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBy(array('slug' => $input->getArgument('slug')));
        $updater = $this->getContainer()->get($shop->getUpdaterName());
        
        // Load the affiliate service
        $affiliateService = $this->getContainer()->get('threadandmirror.affiliate.'.$shop->getAffiliateName());

        // Set the updater
        $affiliateService->setUpdater($updater);

        // Update the products using the feed files
        $affiliateService->createProductsFromFeed($shop);
    }
}