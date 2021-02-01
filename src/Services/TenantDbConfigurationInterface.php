<?php


namespace byhaskell\DoctrineDbSwitcherBundle\Services;

/**
 * @author Ramy byhaskell <pencilsoft1@gmail.com>
 */
Interface TenantDbConfigurationInterface
{
    /**
     * Tenant database name
     * @return string
     */
    public function getDbName();

    /**
     * Tenant database user name
     * @return string
     */
    public function getDbUsername();

    /**
     * Tenant database password
     * @return mixed|null
     */
    public function getDbPassword();
}