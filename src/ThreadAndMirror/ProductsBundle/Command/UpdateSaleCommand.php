<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSaleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:updateSale')
            ->setDescription('Performs a crawl of a given shop to find newly added sale products.')
            ->addArgument('slug', InputArgument::REQUIRED, 'The slug of the shop to be parsed.')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the shop to be parsed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // load the necessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // get the products
        $existing = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getExistingProductIds($input->getArgument('id'));

        // build the class name for the parser
        $class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $input->getArgument('slug'))).'Parser';

        // load the relevant shop parser for the for the product
        $parser = new $class($this->getContainer()->get('threadandmirror.product.parser'));

        // run the latest additions parser
        $results = $parser->sale($existing);

        // flush any remaining updated products and notify complete
        $output->writeln($results.' products added.');
    }
}