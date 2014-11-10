<?php

namespace ThreadAndMirror\ProductsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LinkshareParseFeedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('linkshare:parseFeed')
            ->setDescription('Loads products from an affiliate\'s datafeed and adds them to the database, skipping any that already exist.')
            ->addArgument('slug', InputArgument::REQUIRED, 'The shop that the products belong to, or \'all\' to cycle through all shops.')
            ->addArgument('type', InputArgument::REQUIRED, 'The feed file to use, either \'full\' or \'delta\'.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // load the necessary services
        $feed = $this->getContainer()->get('threadandmirror.linkshare');

        // run the relevant service
        $total = $feed->parseProducts($input->getArgument('slug'), $input->getArgument('type'));
        $output->writeln($total.' products added.');
    }
}