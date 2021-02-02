# Doctrine Db Switcher Bundle  


Doctrine db switcher bundle os an easy way to support multi-tenant databases in your symfony application, Which is very helpful to extend doctrine to manage multiple databases with one doctrine entity manager where you can switch between all of them in Runtime
 
 ##### This bundle provides this list of features :  
 
  - Switch between tenant databases on runtime easily by dispatch  an event.
  - Supporting different entities mapping for main and tenant entities.
  - Provide custom extended doctrine commands to manage tenant databases independently. 
  - Generate and run migrations independently of your main database.
  - Execute bulk migrations for all tenants dbs with one command (soon).
  - Create and prepare tenant database if not exist

  

### Installation

This bundle requires 
- [Symfony](https://symfony.org/) v5+ to run.
- [Doctrine Bundle](https://github.com/doctrine/DoctrineBundle)
- [Doctrine Migration Bundle](https://github.com/doctrine/DoctrineMigrationsBundle) v3+ to run 


Install using Composer

```sh
$ composer require byhaskell/doctrine-db-switcher-bundle
``` 
 ### Using the Bundle
 ###### The idea behind this bundle is simple,You have a main database and  multi-tenant databases So: 
 1. Create specific entity witch should implement `TenantDbConfigurationInterface`. In your main database to save all tenant databases configurations. 
 2. Add `TenantEntityManager` to your service or controller arguments.  
 3. Dispatch `SwitchDbEvent` with a custom value for your tenant db Identifier.
    `Example new SwitchDbEvent(1)`
 4. You can switch between all tenants dbs just by dispatch the same event with different db identifier.
 5. Now your instance from `TenantEntityManager` is connected to the tenant db with Identifier = 1.
 6. Its recommended having your tenant entities in a different directory from your Main entities.
 7. You can execute doctrine migration commands using our proxy commands for tenant database.
 
        php bin/console tenant:migration:diff 1   # t:m:d 1 for short , To generate migraiton for tenant db  => 1
        
        php bin/console tenant:migration:migrate 1  # t:m:m 1, To run migraitons for tenant db  => 1
        
        # Pass tenant identifier is optional and if it null the command will be executed on the defualt tenant db. 
        # You can use the same options here for the same doctrine commands.
        
### Note:
  All the doctrine migration commands and files is generated and executed especially for tenant databases independent of main db migrations, 
   Thanks for Doctrine migration bundle v3+ .
   
### Usage Example 
 You can dispatch the event where ever you want to switch to custom db
   
   ```php
      namespace App\Controller;
    
    
      use Symfony\Component\EventDispatcher\EventDispatcherInterface;  
      use byhaskell\DoctrineDbSwitcherBundle\Event\SwitchDbEvent;
      use byhaskell\DoctrineDbSwitcherBundle\Doctrine\ORM\TenantEntityManager;
      use Doctrine\ORM\EntityManagerInterface;
      use App\Entity\Tenant\TenantEntityExample;
      use App\Entity\Main\MainLog;


       public class AccountController extends AbstractController
       {
    
           /**
            * @var EntityManagerInterface
            */
           private $mainEntityManager;
           /**
            * @var TenantEntityManager
            */
           private $tenantEntityManager;
           /**
            * @var EventDispatcherInterface
            */
           private $dispatcher;
    
        public function __construct(
                EntityManagerInterface $entityManager,
                TenantEntityManager $tenantEntityManager,
                EventDispatcherInterface $dispatcher)
            {
                $this->mainEntityManager = $entityManager;
                $this->tenantEntityManager = $tenantEntityManager;
                $this->dispatcher = $dispatcher;
            }
    
        public function updateTenantAccount(TenantEntityExample $tenantEntityExample)
            {
                   // switch connection to tenant account database
    
                  $switchEvent = new SwitchDbEvent($tenantEntityExample->getDbConfigId());
                  $this->dispatcher->dispatch($switchEvent);
    
                  // now $tenantEntityManager is connected to custom tenant db
    
                  $tenantEntityExample->updateSomthing();
                  $this->tenantEntityManager->persist($tenantEntityExample);
                  $this->tenantEntityManager->persist();
    
                  //log update action in our main db 
    
                  $mainLog =new MainLog($tenantEntityExample->getId());
                  $this->mainEntityManager->persist($mainLog);
                  $this->mainEntityManager->flush();
            }
    
           //..
       }
   ```

 ### Configuration
 
 In this example below you can find the list of all configuration parameters required witch you should create in
   `config/packages/byhaskell_doctrine_db_switch_bundle.yaml` with this configuration:
 ``` yaml 
byhaskell_doctrine_db_switcher:
  tenant_database_className:  App\Entity\Main\TenantDbConfig     # tenant dbs configuration Class Name
  tenant_database_identifier: id                                 # tenant db column name to get db configuration
  tenant_database_manager: 'doctrine.orm.default_entity_manager' # service ID entity manager through which you can connect with the entity tenant_database_className
  tenant_connection:                                             # tenant entity manager connection configuration
    host:     127.0.0.1
    driver:   pdo_mysql
    charset:  utf8 
    dbname:   tanent1                                           # default tenant database to init the tenant connection
    user:     root                                              # default tenant database username
    password: null                                              # default tenant database password
  tenant_migration:                                             # tenant db migration configurations, Its recommended to have a different migration for tenants dbs than you main migration config
    tenant_migration_namespace: Application\Migrations\Tenant
    tenant_migration_path: migrations/Tenant
  tenant_entity_manager:                                        # tenant entity manger configuration , which is used to manage tenant entities
    mapping:                                                  
      type:   annotation                                        # mapping type default annotation                                                       
      dir:   '%kernel.project_dir%/src/Entity/Tenant'           # directory of tenant entities, it could be different from main directory                                           
      prefix: App\Entity\Tenant                                 # tenant entities prefix  ex "App\Entity\Tenant"
      alias:   Tenant                                           # tenant entities alias  ex "Tenant"
    dql:
      string_functions:
        MD5: '\DoctrineExtensions\Query\Mysql\Md5'
        regexp: '\DoctrineExtensions\Query\Mysql\Regexp'
        date_format: '\DoctrineExtensions\Query\Mysql\DateFormat'
        year: '\DoctrineExtensions\Query\Mysql\Year'
        day: '\DoctrineExtensions\Query\Mysql\Day'
        NOW: '\DoctrineExtensions\Query\Mysql\Now'
        date_diff: '\DoctrineExtensions\Query\Mysql\DateDiff'
        date: '\DoctrineExtensions\Query\Mysql\Date'
        date_add: '\DoctrineExtensions\Query\Mysql\DateAdd'
        date_sub: '\DoctrineExtensions\Query\Mysql\DateSub'
 ```
Attention! dql is optional

###Configuration Doctrine

Example configuration doctrine.yaml

```
doctrine:
    dbal:
        default_connection: tenant
        connections:
            tenant:
                url: '%env(DATABASE_URL_TENANT)%'
                driver: 'pdo_mysql'
                server_version: '5.7'
                charset: utf8mb4
                default_table_options:
                    charset: utf8mb4
                    collate: utf8mb4_unicode_ci
                wrapper_class: byhaskell\DoctrineDbSwitcherBundle\Doctrine\DBAL\TenantConnection
    orm:
        default_entity_manager: tenant
        entity_managers:
            tenant:
                connection: tenant
                mappings:
                    tenant:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/src/Entity/Tenant'
                        prefix: 'App\Entity\Tenant'
                        alias: tenant
                dql:
                    string_functions:
                        MD5: DoctrineExtensions\Query\Mysql\Md5
                        regexp: DoctrineExtensions\Query\Mysql\Regexp
                        date_format: DoctrineExtensions\Query\Mysql\DateFormat
                        year: DoctrineExtensions\Query\Mysql\Year
                        day: DoctrineExtensions\Query\Mysql\Day
                        NOW: DoctrineExtensions\Query\Mysql\Now
                        date_diff: DoctrineExtensions\Query\Mysql\DateDiff
                        date: DoctrineExtensions\Query\Mysql\Date
                        date_add: DoctrineExtensions\Query\Mysql\DateAdd
                        date_sub: DoctrineExtensions\Query\Mysql\DateSub
```
Often other bundles request a connection `default`, so we recommend leaving the default one and adding a new one.

### Custom EventListener

For the correct choice of the database, in most cases the proposed default option is not suitable. Customize.

```
<?php

namespace App\EventListener;

use App\Doctrine\DBAL\TenantConnection;
use App\Entity\Tenant\Users;
use App\Repository\TenantRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    private $storage;

    public function __construct(ContainerInterface $container,TenantRepository $tenantRepository, TokenStorageInterface $storage)
    {
        $this->container = $container;
        $this->tenantRepository = $tenantRepository;
        $this->storage = $storage;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest'
        ];
    }

    public function onKernelRequest( RequestEvent $event): void
    {
        //Переменная для объекта с данными подключаемой базы
        $tenant = null;
        //Определяем пользователя что бы определить к какой базе получить доступ.
        //По дефолту берем первую базу так как она основная
        if(!empty($this->storage->getToken()) && $this->storage->getToken()->getUser() instanceof Users) {
            $referer = $event->getRequest()->headers->get('referer');
            if(!empty($referer)){
                if(stripos($referer, '//') !== false){
                    $referer = substr($referer, stripos($referer, '//')+2);
                }
                if(stripos($referer, '/') !== false){
                    $referer = substr($referer, 0, stripos($referer, '/'));
                }
            }
            $tenant = $this->tenantRepository->findOneBy( ['domain' => $referer ] );
        }
        //Если пустой, значит не смогли определить пользователя и включаемся в основную базу данных
        if($tenant === null){
            $tenant = $this->tenantRepository->find(1);
        }

        /**
         * @var TenantConnection $tenantConnection
         */
        $tenantConnection = $this->container->get('doctrine')->getConnection('tenant');
        $tenantConnection->changeParams($tenant->getDbName(), $tenant->getDbUserName(), $tenant->getDbPassword());
        $tenantConnection->reconnect();
    }
}
```

### Contribution

Want to contribute? Great!
 - Fork your copy from the repository
 - Add your new Awesome features 
 - Write MORE Tests
 - Create a new Pull request 

License
----

# MIT
**Free Software, Hell Yeah!**
