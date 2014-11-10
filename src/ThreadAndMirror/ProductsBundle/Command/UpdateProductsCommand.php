<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProductsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:affiliate:update')
            ->setDescription('Update products from the shops\'s affiliate API')
            ->addArgument('slug', InputArgument::REQUIRED, 'The shop to update products for.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load the necessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get the shop
        $shop = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBySlug($input->getArgument('slug'));

        // Get the product list
        if ($shop->getAffiliateName() !== null) {
            $products = $this->getContainer()->get('threadandmirror.affiliate_window.api')->setMode('productServe')->setMerchant($this->getAffiliateId())->getMerchantProducts();
        }

        var_dump($products);

        $output->writeln('done.');
    }
}