<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateIndividualCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('products:updateIndividual')
            ->setDescription('Performs a crawl to update a given amount of products.')
            ->addArgument('limit', InputArgument::REQUIRED, 'The amount of products to be processed.')
            ->addArgument('slug', InputArgument::REQUIRED, 'The shop that the products belong to.')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the shop.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // load the necessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // get the products
        $limit = $input->getArgument('limit');
        $products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getUpdateable($limit, $input->getArgument('id'));
        $i = 0;

        foreach ($products as $product) {

            // build the class name for the parser
            $class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $input->getArgument('slug'))).'Parser';

            // load the relevant shop parser for the for the product
            $parser = new $class($this->getContainer()->get('threadandmirror.product.parser'));
            
            // run the update parser
            $updated = $parser->update($product);
            
            // save and mark as checked
            $updated->setChecked(new \DateTime());
            $output->writeln($updated->getUrl().' (PID '.$updated->getId().') processed.');
            $em->persist($updated);
            $em->flush();

            // cleanup up the parser and any new version of the product
            unset($updated);
            unset($parser); 
        }

        // flush any remaining updated products and notify complete
        $output->writeln('Done.');
    }
}