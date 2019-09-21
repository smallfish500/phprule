<?php
/**
 * Database trait file
 *
 * PHP version 7
 *
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
namespace Rule;

/**
 * Database trait
 *
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
trait Database
{
    /**
     * Get the database connection
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function getDatabase()
    {
        static $conn;
        if (empty($conn)) {
            $config = new \Doctrine\DBAL\Configuration();
            if (DEBUG) {
                $config->setSQLLogger(new \Doctrine\DBAL\Logging\DebugStack());
            }
            $conn = \Doctrine\DBAL\DriverManager::getConnection(
                ['url' => DATABASE_URL],
                $config
            );
        }

        return $conn;
    }
}
