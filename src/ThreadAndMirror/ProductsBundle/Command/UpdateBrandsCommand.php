<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThreadAndMirror\ProductsBundle\Entity\Brand;

class UpdateBrandsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('threadandmirror:update:brands')
            ->setDescription('Assigns brand IDs to products using brand names and creating where necessary.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load the necessary services
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get the products
        $products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findUpdateableForBrands();

        $newBrands = array();

        foreach ($products as $product) {

            // See if the brand already exists
            $brand = $em->getRepository('ThreadAndMirrorProductsBundle:Brand')->findOneBy(array('name' => $product->getBrandName()));

            if ($brand !== null) {
                $product->setBrand($brand);
                $output->writeln($product->getId().' updated.');
                $product->setChecked(new \DateTime());
                $em->persist($product);
            } else {

                if (array_key_exists($product->getBrandName(), $newBrands)) {
                    $product->setBrand($newBrands[$product->getBrandName()]);
                    $output->writeln($product->getId().' updated.');
                    $product->setChecked(new \DateTime());
                    $em->persist($product);
                } else {
                    $brand = new Brand();
                    $brand->setName($product->getBrandName());

                    if ($product->getArea() === 'fashion') {
                        $brand->setHasFashion(true);
                    } elseif ($product->getArea() === 'beauty') {
                        $brand->setHasBeauty(true);
                    }
                    $product->setChecked(new \DateTime());
                    $em->persist($brand);
                    $em->persist($product);
                    $em->flush();

                    $newBrands[$brand->getName()] = $brand;

                    $output->writeln($product->getId().' updated and brand '.$brand->getName().' created.');
                }   
            }  
        }

        // Flush any remaining updated products and notify complete
        $em->flush();
    }
}