<?php
namespace WhereGroup\Spatialite;

use Traversable;

/**
 * Class Statement
 *
 *
 * * Wrapper for SQLite3Stmt
 *
 * TODO Issues:
 * https://www.google.de/?gws_rd=ssl#hl=de&q=SELECT+InitSpatialMetadata()+loadExtension
 * http://stackoverflow.com/questions/601600/how-to-get-the-number-of-rows-of-the-selected-result-from-sqlite3
 * http://stackoverflow.com/questions/9652972/whenever-i-use-either-loadextension-or-select-load-extension-it-gives-internal-s
 *
 * @package WhereGroup\Spatialite
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Statement implements \IteratorAggregate, \Doctrine\DBAL\Driver\Statement
{
    /**
     * Fetch mode mapping array
     *
     * @var array
     */
    protected static $fetchModes = array(
        \PDO::FETCH_ASSOC => "SQLITE3_ASSOC",
        \PDO::FETCH_NUM   => "SQLITE3_NUM",
        \PDO::FETCH_BOTH  => 'SQLITE3_BOTH'
    );

    /**
     * @var array
     */
    protected static $paramTypes = array(
        \PDO::PARAM_STR => 's',
        \PDO::PARAM_BOOL => 'i',
        \PDO::PARAM_NULL => 's',
        \PDO::PARAM_INT => 'i',
        \PDO::PARAM_LOB => 's' // TODO Support LOB bigger then max package size.
    );

    /**
     * @var \SQLite3
     */
    protected $conn;

    /**
     * @var \SQLite3Result
     */
    protected $result;

    /**
     * @var int Fetch mode
     */
    protected $fetchMode;

    /**
     * @var \SQLite3Stmt Prepared SQL statement to execute.
     */
    private $stmt;

    /**
     * Constructor.
     *
     * Prepares given statement
     *
     * @param \SQLite3 $conn The conneciton resource to user
     * @param string   $sql  SQL to be prepared
     * @throws \Exception
     */
    public function __construct(\SQLite3 $conn, $sql = null)
    {
        $this->conn = $conn;

        if ($sql) {
            $this->sql  = $sql;
            $this->stmt = $conn->prepare($sql);
        }

        if (!$this->stmt) {
            throw new \Exception($conn->lastErrorMsg(), $conn->lastErrorCode());
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fetchAll());
    }

    /**
     * Closes the cursor, enabling the statement to be executed again.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function closeCursor()
    {
        return $this->stmt->close();
    }

    /**
     * Returns the number of columns in the result set
     *
     * @return integer The number of columns in the result set represented
     *                 by the PDOStatement object. If there is no result set,
     *                 this method should return 0.
     */
    public function columnCount()
    {
        return $this->result->numColumns();
    }

    /**
     * Sets the fetch mode to use while iterating this statement.
     *
     * @param integer $fetchMode The fetch mode must be one of the PDO::FETCH_* constants.
     * @param mixed   $arg2
     * @param mixed   $arg3
     *
     * @return boolean
     *
     * @see PDO::FETCH_* constants.
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        $this->fetchMode = $fetchMode;
    }

    /**
     * Returns the next row of a result set.
     *
     * @param integer|null $fetchMode Controls how the next row will be returned to the caller.
     *                                The value must be one of the PDO::FETCH_* constants,
     *                                defaulting to PDO::FETCH_BOTH.
     *
     * @return mixed The return value of this method on success depends on the fetch mode. In all cases, FALSE is
     *               returned on failure.
     *
     * @see PDO::FETCH_* constants.
     */
    public function fetch($fetchMode = null)
    {
        return $this->result->fetchArray(self::$fetchModes[$fetchMode]);
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param integer|null $fetchMode Controls how the next row will be returned to the caller.
     *                                The value must be one of the PDO::FETCH_* constants,
     *                                defaulting to PDO::FETCH_BOTH.
     *
     * @return array
     *
     * @see PDO::FETCH_* constants.
     */
    public function fetchAll($fetchMode = null)
    {
        $rows = array();
        while ($row = $this->fetch($fetchMode)) {
            $rows[$row];
        }
        return $rows;
    }

    /**
     * Returns a single column from the next row of a result set or FALSE if there are no more rows.
     *
     * @param integer $columnIndex 0-indexed number of the column you wish to retrieve from the row.
     *                             If no value is supplied, PDOStatement->fetchColumn()
     *                             fetches the first column.
     *
     * @return string|boolean A single column in the next row of a result set, or FALSE if there are no more rows.
     */
    public function fetchColumn($columnIndex = 0)
    {
        $row = array_values($this->fetch());
        return $row[$columnIndex];
    }

    /**
     * Binds a value to a corresponding named or positional
     * placeholder in the SQL statement that was used to prepare the statement.
     *
     *
     * @param mixed   $param Parameter identifier. For a prepared statement using named placeholders,
     *                       this will be a parameter name of the form :name. For a prepared statement
     *                       using question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed   $value The value to bind to the parameter.
     * @param integer $type  Explicit data type for the parameter using the PDO::PARAM_* constants.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    function bindValue($param, $value, $type = null)
    {
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Binds a PHP variable to a corresponding named (not supported by mysqli driver, see comment below) or question
     * mark placeholder in the SQL statement that was use to prepare the statement. Unlike PDOStatement->bindValue(),
     * the variable is bound as a reference and will only be evaluated at the time
     * that PDOStatement->execute() is called.
     *
     * As mentioned above, the named parameters are not natively supported by the mysqli driver, use executeQuery(),
     * fetchAll(), fetchArray(), fetchColumn(), fetchAssoc() methods to have the named parameter emulated by doctrine.
     *
     * Most parameters are input parameters, that is, parameters that are
     * used in a read-only fashion to build up the query. Some drivers support the invocation
     * of stored procedures that return data as output parameters, and some also as input/output
     * parameters that both send in data and are updated to receive it.
     *
     * @param mixed        $column   Parameter identifier. For a prepared statement using named placeholders,
     *                               this will be a parameter name of the form :name. For a prepared statement using
     *                               question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed        $variable Name of the PHP variable to bind to the SQL statement parameter.
     * @param integer|null $type     Explicit data type for the parameter using the PDO::PARAM_* constants. To return
     *                               an INOUT parameter from a stored procedure, use the bitwise OR operator to set the
     *                               PDO::PARAM_INPUT_OUTPUT bits for the data_type parameter.
     * @param integer|null $length   You must specify maxlength when using an OUT bind
     *                               so that PHP allocates enough memory to hold the returned value.
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    function bindParam($column, &$variable, $type = null, $length = null)
    {
        $this->stmt->bindParam($column, $variable, $type);
    }

    /**
     * Fetches the SQLSTATE associated with the last operation on the statement handle.
     *
     * @see Doctrine_Adapter_Interface::errorCode()
     *
     * @return string The error code string.
     */
    function errorCode()
    {
        $this->conn->lastErrorCode();
    }

    /**
     * Fetches extended error information associated with the last operation on the statement handle.
     *
     * @see Doctrine_Adapter_Interface::errorInfo()
     *
     * @return array The error info array.
     */
    function errorInfo()
    {
        $this->conn->lastErrorMsg();
    }

    /**
     * Executes a prepared statement
     *
     * If the prepared statement included parameter markers, you must either:
     * call PDOStatement->bindParam() to bind PHP variables to the parameter markers:
     * bound variables pass their value as input and receive the output value,
     * if any, of their associated parameter markers or pass an array of input-only
     * parameter values.
     *
     *
     * @param array|null $params An array of values with as many elements as there are
     *                           bound parameters in the SQL statement being executed.
     * @return bool TRUE on success or FALSE on failure.
     */
    function execute($params = null)
    {
        $this->result = $this->stmt->execute();
        return !!$this->result;
    }

    /**
     * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement
     * executed by the corresponding object.
     *
     * If the last SQL statement executed by the associated Statement object was a SELECT statement,
     * some databases may return the number of rows returned by that statement. However,
     * this behaviour is not guaranteed for all databases and should not be
     * relied on for portable applications.
     *
     * It's no normal way to get the number.
     *
     * @return integer The number of rows.
     */
    function rowCount()
    {
        return $this->conn->changes();
    }

}