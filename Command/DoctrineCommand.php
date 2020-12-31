<?php

namespace Doctrine\Bundle\DoctrineBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Base class for Doctrine console commands to extend from.
 *
 * @internal
 */
abstract class DoctrineCommand extends Command
{
    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();

        $this->doctrine = $doctrine;
    }

    /**
     * get a Viva entity generator
     *
     * @return \VivaEntityGenerator
     */
    protected function getEntityGenerator()
    {
        require_once '/opt/db-migration/VivaEntityGenerator.php';

        /* @var PostgreSqlSchemaManager $schemaManager */
        $schemaManager = $this->getDoctrine()->getConnection()->getSchemaManager();

        return new \VivaEntityGenerator($schemaManager);
    }

    /**
     * Get a doctrine entity manager by symfony name.
     *
     * @param string   $name
     * @param int|null $shardId
     *
     * @return EntityManager
     */
    protected function getEntityManager($name, $shardId = null)
    {
        $manager = $this->getDoctrine()->getManager($name);

        if ($shardId) {
            if (! $manager->getConnection() instanceof PoolingShardConnection) {
                throw new LogicException(sprintf("Connection of EntityManager '%s' must implement shards configuration.", $name));
            }

            $manager->getConnection()->connect($shardId);
        }

        return $manager;
    }

    /**
     * Get a doctrine dbal connection by symfony name.
     *
     * @param string $name
     *
     * @return Connection
     */
    protected function getDoctrineConnection($name)
    {
        return $this->getDoctrine()->getConnection($name);
    }

    /**
     * @return ManagerRegistry
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }
}
