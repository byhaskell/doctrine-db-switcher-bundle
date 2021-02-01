<?php


namespace byhaskell\DoctrineDbSwitcherBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Ramy byhaskell <pencilsoft1@gmail.com>
 */
class SwitchDbEvent extends Event
{
    /**
     * @var string
     */
    private $dbIndex;

    public function __construct( string $tenantDbIndex)
   {
       $this->dbIndex = $tenantDbIndex;
   }

    /**
     * @return string
     */
    public function getDbIndex(): string
    {
        return $this->dbIndex;
    }
}