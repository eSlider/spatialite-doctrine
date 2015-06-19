<?php
namespace WhereGroup\Spatialite;

/**
 * Class SpatialiteDriver
 *
 * @package   Spatilite\Drivers
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 */
class Driver extends AbstractDriver
{
    /**
     * @var \SQLite3 connection link
     */
    protected $conn;

    /**
     * Attempts to create a connection with the database.
     *
     * @param array       $params        All connection parameters passed by the user.
     * @param string|null $username      The username to use when connecting.
     * @param string|null $password      The password to use when connecting.
     * @param array       $driverOptions The driver options to use when connecting.
     *
     * @return \Doctrine\DBAL\Driver\Connection The database connection.
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        return new Connection($params);
    }

    public function initSpatialite()
    {
        # loading SpatiaLite as an extension
        $this->conn->exec("SELECT load_extension('mod_spatialite')");
        $extensionPath = isset($params['extensionPath']) ? $params['extensionPath'] : 'mod_spatialite.dll';
        $this->conn->loadExtension($extensionPath);

        # enabling Spatial Metadata
        # using v.2.4.0 this automatically initializes SPATIAL_REF_SYS
        # and GEOMETRY_COLUMNS
        $this->exec("SELECT InitSpatialMetadata()");
    }

    /**
     * Gets the name of the driver.
     *
     * @return string The name of the driver.
     */
    public function getName()
    {
        return "Spatialite";
    }

    /**
     * @param $sql
     * @return Boolean
     */
    public function exec($sql)
    {
        return $this->conn->exec($sql);
    }

}