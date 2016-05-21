<?php

namespace ThreadAndMirror\BlogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ThreadAndMirror\BlogBundle\Entity\Section;

class CleanupSectionPositionsCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
    protected function configure()
    {
        $this
            ->setName('thread:blog:cleanup-sections')
            ->setDescription('Tidy up section positions for old posts.')
        ;
    }

	/**
	 * {@inheritdoc}
	 */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

	    /** @var Section[] $sections */
        $sections = $em->getRepository('ThreadAndMirrorBlogBundle:Section')->findAll();

        foreach ($sections as $section) {

	        $currentY = $section->getY();
	        $newY = $currentY - ($currentY % 15);

	        $section->setY($newY);

	        $em->persist($section);
	        $em->flush();
        }
    }
}