<?php
namespace WhereGroup\Spatialite;

use Doctrine\DBAL\Schema\SqliteSchemaManager;

/**
 * Class SchemaManager
 *
 * @package   Spatilite\Drivers
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 */
class SchemaManager extends SqliteSchemaManager
{
    /**
     * {@inheritdoc}
     */
    public function createDatabase($database)
    {
        $options = array_merge($this->_conn->getParams(), array('path' => $database));
        $conn    = \Doctrine\DBAL\DriverManager::getConnection($options);
        $conn->connect();
        $conn->close();
    }
}