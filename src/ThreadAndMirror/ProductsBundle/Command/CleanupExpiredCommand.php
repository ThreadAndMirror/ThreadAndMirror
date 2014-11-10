<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupExpiredCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:cleanupExpired')
            ->setDescription('Sets any products that have been out of stock for over a mont has no longer available.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // load the necessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // get the products that have been out of stock for over a month and are not marked as expired
        $expiring = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getExpiringProducts();

        // mark as expired
        foreach ($expiring as $product) {
            $product->setExpired(new \DateTime());
            $em->persist($product);
        }

        // flush any remaining updated products and notify complete
        $em->flush();
        $output->writeln(count($expiring).' products marked as expired.');
    }
}