<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlShopProductsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:crawl:shop')
            ->setDescription('Performs a crawl to update a given amount of products for a shop.')
            ->addArgument('shop', InputArgument::REQUIRED, 'The slug of the shop to crawl products for.')
            ->addArgument('limit', InputArgument::OPTIONAL, 'The amount of products to be processed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shop = $input->getArgument('shop');

        // Load the necessary services
        $em      = $this->getContainer()->get('doctrine.orm.entity_manager');
        $names   = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getServiceNames($shop);
        $updater = $this->getContainer()->get($names['updater']);

        // get the products
        // $limit = $input->getArgument('limit');
        $products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findUpdateableByShop($shop);

        foreach ($products as &$product) {

            // Update the product based on new data
            $output->writeln('Updating product '.$product->getId().' using url '.$product->getUrl());
            $updater->updateProductFromCrawl($product);
            
            // Save and mark as checked
            $product->setChecked(new \DateTime());
            $em->persist($product);
            $em->flush();

            if ($product->getExpired() !== null) {
                $output->writeln('Product has expired.');
            } else {
                $output->writeln('Product updated.');
            }
        }

        $output->writeln('Done.');
    }
}