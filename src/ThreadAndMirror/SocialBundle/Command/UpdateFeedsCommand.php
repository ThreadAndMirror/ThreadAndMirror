<?php

namespace ThreadAndMirror\SocialBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFeedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('socialCircle:updateFeeds')
            ->setDescription('Updates all feed of the specific type.')
            ->addArgument('type', InputArgument::REQUIRED, 'The feed type to update.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // run the update service and store the errors count, if any
        $errors = $this->getContainer()->get('threadandmirror.social.feeds')->updateFeeds($input->getArgument('type'));

        // set the completion message and redirect to the social overview
        if ($errors) {
            $output->writeln('Some '.ucfirst($input->getArgument('type')).' feeds were updated, but there were '.$errors.' failures');
        } else {
            $output->writeln('All '.ucfirst($input->getArgument('type')).' feeds were updated');
        }
    }
}