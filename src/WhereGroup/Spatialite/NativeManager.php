<?php
namespace WhereGroup\Spatialite;

/**
 * Class Spatialite
 *
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 * @see       http://www.gaia-gis.it/gaia-sins/spatialite-cookbook/html/new-geom.html
 * @see       http://www.gaia-gis.it/gaia-sins/splite-doxy-4.2.0/annotated.html
 */
class NativeManager
{
    /**
     * @var \SQLite3
     */
    protected $db;

    /**
     * @param string $dbFile file path, by default saves data in RAM
     */
    public function __construct($dbFile = ':memory:')
    {
        $this->db = new \SQLite3($dbFile);
        # loading SpatiaLite as an extension
        $isWindows = strpos( $_SERVER["OS"],"Windows") === 0;
        $is64 = strpos($_SERVER["PROCESSOR_ARCHITECTURE"],"64") > 0;
//        $libSrc    = 'bin/'.($isWindows?'win':'lin').'-'.($is64?'amd64':'x86').'/mod_spatialite'.($isWindows ? '.dll' : '.so');
        $libSrc    = 'mod_spatialite';//($isWindows ? '.dll' : '.so');
//        $libSrc = "C:\\Projects\\spatialite-doctrine\\bin\\win-amd64\\".$libSrc;
//        $this->db->loadExtension($libSrc);
        $this->db->exec("SELECT sqlite_version()"); //  load_extension('mod_spatialite'
        # enabling Spatial Metadata
        # using v.2.4.0 this automatically initializes SPATIAL_REF_SYS

        # and GEOMETRY_COLUMNS
//        $this->exec("SELECT InitSpatialMetadata()");
    }

    /**
     * @param $sql
     * @return array
     */
    public function fetchAll($sql)
    {
        /** @var \SQLite3Result $rs */
        $rs   = $this->db->query($sql);
        $rows = array();
        while ($row = $rs->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function fetchOne($sql)
    {
        $result = $this->fetchAll($sql);
        return is_array($result) ? current($result) : null;
    }

    /**
     * @param     $sql
     * @param int $column
     * @return null|array
     */
    public function fetchColumn($sql, $column = 0)
    {
        $result = array_values($this->fetchOne($sql));
        return is_array($result) ? $result[$column] : null;
    }

    /**
     * Start transaction
     */
    public function startTransaction()
    {
        $this->exec("BEGIN");
    }

    /**
     * Stop transaction
     */
    public function stopTransaction()
    {
        $this->exec("BEGIN");
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->fetchOne("SELECT
        geos_version() as geos,
        proj4_version() as proj4,
        sqlite_version() as sqlite,
        spatialite_version() as spatilite,
        spatialite_target_cpu() as targetCpu"
        );
    }

    /**
     * Query SQL
     *
     * @param $sql
     * @return bool
     */
    public function exec($sql)
    {
        return $this->db->exec($sql);
    }

    /**
     * @param        $tableName
     * @param string $columnName
     * @param int    $srid
     * @param string $type
     * @return bool
     */
    public function addGeometryColumn($tableName, $columnName = "geom", $srid = 4326, $type = "POLYGON")
    {
        return $this->exec("SELECT AddGeometryColumn('$tableName', '$columnName', $srid, '$type', 'XY')");
    }

    /**
     * @param $name
     * @return array
     */
    public function getTableInfo($name)
    {
        $info = $this->fetchAll("PRAGMA table_info(" . $name . ")");
        foreach ($info as &$column) {
            $column["geometry"] = $this->fetchOne("SELECT * FROM geometry_columns WHERE f_table_name LIKE '$name' AND f_geometry_column LIKE '{$column["name"]}'");
        }
        return $info;
    }

    /**
     * @param $tableName
     * @param $columnName
     * @return null|array
     */
    public function getColumnInfo($tableName, $columnName)
    {
        foreach ($this->getTableInfo($tableName) as &$column) {
            if ($column['name'] = $columnName) {
                return $column;
            }
        }
    }

    /**
     * Get table SRID
     *
     * @param      $tableName
     * @param null $columnName
     * @return null
     */
    public function getSrid($tableName, $columnName = null)
    {

        return $this->fetchColumn("SELECT srid FROM geometry_columns WHERE f_table_name LIKE '$tableName'" . ($columnName ? " AND f_geometry_column LIKE '{$columnName}'" : ''));
    }

    /**
     * @param $wkt
     * @return null
     */
    public function hexFromWkt($wkt)
    {
        return $this->fetchColumn("SELECT Hex(ST_GeomFromText('$wkt'))");
    }

    /**
     * @param $wkt
     * @return null
     */
    public function wkbFromWkt($wkt)
    {
        return $this->fetchColumn("SELECT Hex(ST_AsBinary(ST_GeomFromText('$wkt')))");
    }

    /**
     * @param $wkb
     * @return null
     * @internal param $wkt
     */
    public function wktFromWkb($wkb)
    {
        return $this->fetchColumn("SELECT ST_AsText(ST_GeomFromWKB(x'$wkb'));");
    }

    /**
     * @param $wkb
     * @internal param $wkt
     * @return null
     */
    public function wktFromHex($wkb)
    {
        return $this->fetchColumn("SELECT ST_AsText(x'$wkb')");
    }

    /**
     * @param string $name    table name
     * @param array  $columns column definitions
     * @return bool
     */
    public function createTable($name, $columns)
    {
        return $this->exec("CREATE TABLE $name (id INTEGER NOT NULL PRIMARY KEY)");
    }

    /**
     * @param $tableName
     * @param $columnName
     * @return null
     */
    public function discardGeometryColumn($tableName, $columnName)
    {
        return $this->fetchColumn("SELECT DiscardGeometryColumn('$tableName', '$columnName'");
    }

    public function recoverGeometryColumn($tableName, $columnName, $srid, $geomType = 'POLYGONE', $coord = 'XY')
    {
        return $this->fetchColumn("SELECT RecoverGeometryColumn('$tableName', '$columnName', $srid, '$geomType', '$coord'");
    }

    /**
     * @param $wkb
     */
    public function getSridFromWkb($wkb)
    {
        $this->fetchColumn("SELECT ST_Srid('$wkb')");

    }

}
