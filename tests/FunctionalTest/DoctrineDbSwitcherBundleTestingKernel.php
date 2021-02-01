<?php


namespace byhaskell\DoctrineDbSwitcherBundle\Tests\FunctionalTest;


use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use byhaskell\DoctrineDbSwitcherBundle\byhaskellDoctrineDbSwitcherBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class DoctrineDbSwitcherBundleTestingKernel extends Kernel
{
    private $doctrineDbSwitcherBundleConfig;

    public function __construct(array $config = [])
    {
        $this->doctrineDbSwitcherBundleConfig = $config;
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
            new byhaskellDoctrineDbSwitcherBundle(),

        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('byhaskell_doctrine_db_switcher', $this->doctrineDbSwitcherBundleConfig);
        });
    }

    public function getProjectDir()
    {
        return __DIR__;
    }
}