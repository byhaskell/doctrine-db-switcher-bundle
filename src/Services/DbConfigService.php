<?php

namespace byhaskell\DoctrineDbSwitcherBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Ramy byhaskell <pencilsoft1@gmail.com>
 */
class DbConfigService
{
    /**
     * @var ServiceEntityRepositoryInterface
     */
    private $entityRepository;
    /**
     * @var string
     */
    private $dbIdentifier;
    /**
     * @var string
     */
    private $serviceIdManager;

    public function __construct(ContainerInterface $container, string $dbClassName, string $dbIdentifier, string $serviceIdManager)
    {
        $this->dbIdentifier = $dbIdentifier;
        $this->entityRepository = $container->get($serviceIdManager)->getRepository($dbClassName);
    }

    public function findDbConfig(string $identifier)
    {
        $dbConfigObject = $this->entityRepository->findOneBy([$this->dbIdentifier => $identifier]);

        if( $dbConfigObject === null )
        {
            throw new RuntimeException(sprintf(
                'Tenant db repository " %s " returns NULL for identifier " %s = %s " ',
                get_class($this->entityRepository),
                $this->dbIdentifier,
                $identifier
            ));
        }

        if( !$dbConfigObject instanceof TenantDbConfigurationInterface)
        {
            throw new LogicException(sprintf(
                'The tenant db entity  " %s ". Should implement " byhaskell\DbSwitcherBundle\TenantDbConfigurationInterface " ',
                get_class($dbConfigObject)
            ));
        }
        return $dbConfigObject;
    }

}