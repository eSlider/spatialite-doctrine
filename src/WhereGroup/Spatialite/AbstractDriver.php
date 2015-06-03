<?php
namespace WhereGroup\Spatialite;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;

/**
 * Class AbstractDriver
 *
 * @package   Spatilite\Drivers
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 */
abstract class AbstractDriver extends AbstractSQLiteDriver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        $params = $conn->getParams();
        return isset($params['path']) ? $params['path'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new Platform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return new SchemaManager($conn);
    }
}