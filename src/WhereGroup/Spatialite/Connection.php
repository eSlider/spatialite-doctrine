<?php
namespace WhereGroup\Spatialite;

use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Connection as IConnection;

/**
 * Class Connection
 *
 * @package WhereGroup\Spatialite
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Connection implements IConnection, ServerInfoAwareConnection
{
    /**
     * @var \SQLite3
     */
    protected $_conn;

    /**
     * Constructor.
     *
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params)
    {
        $this->_conn = new \SQLite3($params["path"]);
        if( !$this->_conn){
            throw new \Exception("Spatilite SQLite3 module isn't loaded!");
        }
    }

    /**
     * Prepares a statement for execution and returns a Statement object.
     *
     * @param string $sql
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    function prepare($sql)
    {
        return new Statement($this->_conn,$sql);
    }

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    function query()
    {
        /** @var Statement $stmt */
        $args = func_get_args();
        $sql  = $args[0];
        $stmt = new Statement($this->_conn, $sql);
        $stmt->execute(count($sql) > 1 ? $args[1] : null);
        return $stmt;
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string  $input
     * @param integer $type
     *
     * @return string
     */
    function quote($input, $type = \PDO::PARAM_STR)
    {
        return \SQLite3::escapeString($input);
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return integer
     */
    function exec($statement)
    {
        $this->_conn->exec($statement);
        return $this->_conn->changes();
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     *
     * @return string
     */
    function lastInsertId($name = null)
    {
        $this->_conn->lastInsertRowID();
    }

    /**
     * Initiates a transaction.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    function beginTransaction()
    {

        $this->exec("PRAGMA foreign_keys=OFF");
        $this->exec("BEGIN TRANSACTION");
    }

    /**
     * Commits a transaction.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    function commit()
    {
        $this->exec("COMMIT");
    }

    /**
     * Rolls back the current transaction, as initiated by beginTransaction().
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    function rollBack()
    {
        $this->exec("ROLLBACK");
    }

    /**
     * Returns the error code associated with the last operation on the database handle.
     *
     * @return string|null The error code, or null if no operation has been run on the database handle.
     */
    function errorCode()
    {
        // TODO: Implement errorCode() method.
    }

    /**
     * Returns extended error information associated with the last operation on the database handle.
     *
     * @return array
     */
    function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }

    /**
     * Returns the version number of the database server connected to.
     *
     * @return string
     */
    public function getServerVersion()
    {
        // TODO: Implement getServerVersion() method.
    }

    /**
     * Checks whether a query is required to retrieve the database server version.
     *
     * @return boolean True if a query is required to retrieve the database server version, false otherwise.
     */
    public function requiresQueryForServerVersion()
    {
        return false;
    }
}