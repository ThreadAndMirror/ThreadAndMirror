<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDiscountCodesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('affiliate:discounts:update')
            ->setDescription('Update discount codes from the affiliate\'s API')
            ->addArgument('affiliate', InputArgument::REQUIRED, 'The affiliate program to update discounts for.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Load the relevant affiliate manager
        $affiliate = $this->getContainer()->get('threadandmirror.affiliate.'.$input->getArgument('affiliate'));

        // Run the discount code updater
        $affiliate->updateDiscountCodes();

        $output->writeln('done.');
    }
}