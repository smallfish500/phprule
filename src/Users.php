<?php
/**
 * User class file
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
 * User class
 * 
 * User and Details model
 * 
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
class Users
{
    use Database;
    use Cache;
    use Template;

    const USER_CACHE_KEY = 'user-';
    const DETAILS_CACHE_KEY = 'user-';

    /**
     * Get one user without the details
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function one($user_id)
    {
        $user = static::_fetchUser($user_id);
        static::show(['users' => [$user]]);
    }

    /**
     * Get the connected user and the details
     *
     * @return void
     */
    public static function me()
    {
        $my_id = 1; // XXX
        $user = static::_fetchUser($my_id);
        $details = static::_fetchDetails($my_id);

        static::show(['user' => $user, 'details' => $details], 'user_details.html');
    }

    /**
     * Get one user with the details
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function details($user_id)
    {
        $user = static::_fetchUser($user_id);
        $details = static::_fetchDetails($user_id);
        static::show(['user' => $user, 'details' => $details], 'user_details.html');
    }

    /**
     * Delete one user with the details
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function delete($user_id)
    {
        // XXX
    }

    /**
     * Create one user
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function post($user_id)
    {
        // XXX
    }

    /**
     * Modify one user
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function modify($user_id)
    {
        // XXX
    }

    /**
     * Enable one user
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function enable($user_id)
    {
        return static::_toggleStatus($user_id, true);
    }

    /**
     * Disable one user
     *
     * @param int $user_id User identifier
     * 
     * @return void
     */
    public static function disable($user_id)
    {
        return static::_toggleStatus($user_id, false);
    }

    /**
     * Toggle one user status
     *
     * @param int  $user_id User identifier
     * @param bool $enabled Enables if true and disables if false
     * 
     * @return void
     */
    private function _toggleStatus($user_id, $enabled)
    {
        static::getDbCache()->delete(static::USER_CACHE_KEY.'-'.$user_id);
        return static::getDatabase()->executeQuery(
            'UPDATE user SET enabled = ? WHERE id = ?',
            [$enabled, $user_id],
            [
                \Doctrine\DBAL\ParameterType::BOOLEAN,
                \Doctrine\DBAL\ParameterType::INTEGER,
            ]
        );
    }

    /**
     * Fetch one user from database
     * or from cache if it has been cached
     *
     * @param int $user_id User identifier
     * 
     * @return array
     */
    private static function _fetchUser($user_id)
    {
        $cache = static::getDbCache();
        $key = static::USER_CACHE_KEY.'-'.$user_id;
        if ($cache->contains($key)) {
            $user = $cache->fetch($key);
        } else {
            $user = static::getDatabase()->executeQuery(
                'SELECT * FROM user WHERE id = ? AND enabled = 1',
                [$user_id],
                [\Doctrine\DBAL\ParameterType::INTEGER]
            )->fetch();
            $cache->save($key, $user);
        }

        return $user;
    }

    /**
     * Fetch one user's details from database
     * or from cache if it has been cached
     *
     * @param int $user_id User identifier
     * 
     * @return array
     */
    private static function _fetchDetails($user_id)
    {
        $cache = static::getDbCache();
        $key = static::DETAILS_CACHE_KEY.'-'.$user_id;
        if ($cache->contains($key)) {
            $details = $cache->fetch($key);
        } else {
            $details_query = 'SELECT detail.label, AES_DECRYPT(value, ?) value '.
                'FROM user_detail ' .
                'INNER JOIN detail ON detail.id = user_detail.detail_id '.
                'WHERE user_id = ?';
            $details = static::getDatabase()->executeQuery(
                $details_query,
                [USER_SECRET, $user_id],
                [
                    \Doctrine\DBAL\ParameterType::BINARY,
                    \Doctrine\DBAL\ParameterType::INTEGER
                ]
            )->fetchAll();
            $cache->save($key, $details);
        }

        return $details;
    }
}

