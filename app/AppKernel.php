<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stems\CoreBundle\StemsCoreBundle(),
            new Stems\PageBundle\StemsPageBundle(),
            new Stems\UserBundle\StemsUserBundle(),
            new Stems\SocialBundle\StemsSocialBundle(),
            new Stems\MediaBundle\StemsMediaBundle(),
            new Stems\PollBundle\StemsPollBundle(),
	        new ThreadAndMirror\CoreBundle\ThreadAndMirrorCoreBundle(),
            new ThreadAndMirror\AlertBundle\ThreadAndMirrorAlertBundle(),
            new ThreadAndMirror\EditorsPicksBundle\ThreadAndMirrorEditorsPicksBundle(),
            new ThreadAndMirror\StreetChicBundle\ThreadAndMirrorStreetChicBundle(),
            new ThreadAndMirror\SocialBundle\ThreadAndMirrorSocialBundle(),
            new ThreadAndMirror\ProductsBundle\ThreadAndMirrorProductsBundle(),
            new ThreadAndMirror\MoodBoardBundle\ThreadAndMirrorMoodBoardBundle(),
            new Ijanki\Bundle\FtpBundle\IjankiFtpBundle(),
            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Ekino\Bundle\NewRelicBundle\EkinoNewRelicBundle(),
            new Snc\RedisBundle\SncRedisBundle(),
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
	        new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new ThreadAndMirror\BlogBundle\ThreadAndMirrorBlogBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
