<?php
use Doctrine\DBAL\DriverManager;
use WhereGroup\Spatialite\NativeManager;

/**
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
 */
class SpatialiteDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group Doctrine driver
     */
    public function testExtendedSqliteDriver()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $conn = DriverManager::getConnection(array(
            'path'        => 'data/spatialite.sqlite',
            'driverClass' => 'WhereGroup\Spatialite\Driver'
        ), $config
        );
        $var = $conn->exec("SELECT sqlite_version()");
        return $var;
    }

    /**
     * @group native manager
     */
    public function nativeManager()
    {
        $dbSrc = "data/test".round(100).".sqlite";
        $cache = new NativeManager($dbSrc);
    }

    //
//    public static function testGeo()
//    {
//        $db    = new NativeManager('../data/spatialite.sqlite');
//        $memDb = (new NativeManager());
//        var_dump($memDb->wkbFromWkt('POINT(1.2345 2.3456)'));
//        var_dump($memDb->wktFromHex('0001FFFFFFFF8D976E1283C0F33F16FBCBEEC9C302408D976E1283C0F33F16FBCBEEC9C302407C010000008D976E1283C0F33F16FBCBEEC9C30240FE'));
//        var_dump($memDb->wktFromWkb('01010000008D976E1283C0F33F16FBCBEEC9C30240'));
//
//        var_dump($db->getVersions());
//
//        echo "<div style='clear: both'/>";
//
//        foreach ($db->fetchAll('SELECT PK_UID, label,
//         AsGml(Geometry) as GML,
//         AsGeoJSON(Geometry) as JSON,
//         AsSVG(Geometry) as SVG,
//         AsKML(Geometry) as KML,
//         ST_AsText(Geometry) as WKT
//         FROM roads LIMIT 100'
//        ) as $row) {
//            $geom = $row['WKT'];
//            $svg  = '<svg style="stroke: #000000; fill:#00ff00; width: 100px; height: 100px; display:block; float: left; border: 1px solid #c0c0c0">
//            <path d="' . $row['SVG'] . '"/>
//            </svg>';
//
//        };
//
//        echo "<div style='clear: both'/>";
//        var_dump($db->getSrid('roads'));
//        var_dump($db->fetchColumn("SELECT COUNT(*) FROM roads WHERE ST_INTERSECTS(Geometry, ST_TRANSFORM(ST_GEOMFROMTEXT('$geom',31467),31467))"));
//    }
}
