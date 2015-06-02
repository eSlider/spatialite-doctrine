<?php
/**
 * 
 * @author Andriy Oblivantsev <eslider@gmail.com>
 * @copyright 02.06.2015 by WhereGroup GmbH & Co. KG
 */
require_once "../vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * Class Spatilite
 *
 * @author    Andriy Oblivantsev <eslider@gmail.com>
 * @copyright 2015 by WhereGroup GmbH & Co. KG
 */
class Spatilite {

}


$paths = array("src/Entity");
$isDevMode = true;
$entityManager = EntityManager::create(array(
    'driverClass' => 'Spatilite',
    'user'        => 'root',
    'password'    => '',
    'dbname'      => 'foo',
), Setup::createAnnotationMetadataConfiguration($paths, $isDevMo3de));