<?php

namespace byhaskell\DoctrineDbSwitcherBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use byhaskell\DoctrineDbSwitcherBundle\Event\SwitchDbEvent;
use byhaskell\DoctrineDbSwitcherBundle\Services\DbConfigService;
use byhaskell\DoctrineDbSwitcherBundle\Services\TenantDbConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Ramy byhaskell <pencilsoft1@gmail.com>
 */
class DbSwitchEventListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DbConfigService
     */
    private $dbConfigService;

    public function __construct(ContainerInterface $container,DbConfigService $dbConfigService)
    {
        $this->container = $container;
        $this->dbConfigService = $dbConfigService;
    }

    public static function getSubscribedEvents()
    {
      return
      [
          SwitchDbEvent::class => 'onbyhaskellDoctrineDbSwitcherBundleEventSwitchDbEvent'
      ];
    }

    public function onbyhaskellDoctrineDbSwitcherBundleEventSwitchDbEvent( SwitchDbEvent $switchDbEvent)
    {
        /** @var TenantDbConfigurationInterface $dbConfig */
        $dbConfig = $this->dbConfigService->findDbConfig($switchDbEvent->getDbIndex());

        $tenantConnection = $this->container->get('doctrine')->getConnection('tenant');
        $tenantConnection->changeParams($dbConfig->getDbName(), $dbConfig->getDbUsername(), $dbConfig->getDbPassword());
        $tenantConnection->reconnect();
    }
}