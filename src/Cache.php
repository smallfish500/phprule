<?php
/**
 * Cache trait file
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
 * Cache trait
 *
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
trait Cache
{
    /**
     * Get the database cache driver
     *
     * @return \Doctrine\Common\Cache\RedisCache
     */
    public static function getDbCache()
    {
        static $driver;
        if (empty($driver)) {
            $redis = new \Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $driver = new \Doctrine\Common\Cache\RedisCache();
            $driver->setRedis($redis);
        }

        return $driver;
    }
}
