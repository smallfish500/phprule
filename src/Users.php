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
    const DETAILS_CACHE_KEY = 'details-';

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
     * Delete one user and related data
     *
     * @param int $user_id User identifier
     *
     * @return void
     */
    public static function delete($user_id)
    {
        $user = static::_fetchUser($user_id);
        $success = static::_delete($user_id);
        static::show(
            ['user' => $user, 'message' => $success ? 'success' : 'failure'],
            'user_action.html'
        );
    }

    /**
     * Create one user
     * 
     * @return void
     */
    public static function post()
    {
        $db = static::getDatabase();
        $insert = $db->executeQuery(
            'INSERT INTO user (label, password, enabled, create_user_id, created) '.
            'VALUES (?, ?, 1, ?, NOW())',
            [
                $_POST['label'],
                $_POST['password'],
                1, // XXX
            ],
            [
                \Doctrine\DBAL\ParameterType::STRING,
                \Doctrine\DBAL\ParameterType::BINARY,
                \Doctrine\DBAL\ParameterType::INTEGER,
            ]
        );

        static::one((int)$db->lastInsertId());
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
        parse_str(file_get_contents('php://input'), $_PATCH);
        $params = [
            'id' => $user_id,
            'update_user_id' => 1, // XXX
        ];
        $types = [
            'id' => \Doctrine\DBAL\ParameterType::INTEGER,
            'update_user_id' => \Doctrine\DBAL\ParameterType::INTEGER,
        ];
        if (!empty($_PATCH['label'])) {
            $params += ['label' => $_PATCH['label']];
            $types +=  ['label' => \Doctrine\DBAL\ParameterType::STRING];
        }
        if (!empty($_PATCH['password'])) {
            $params += ['password' => $_PATCH['password']];
            $types +=  ['label' => \Doctrine\DBAL\ParameterType::BINARY];
        }

        static::getDatabase()->executeQuery(
            'UPDATE user  SET '.
            (!empty($_PATCH['label']) ? 'label = :label, ' : '').
            (!empty($_PATCH['password']) ? 'password = :password, ' : '').
            'update_user_id = :update_user_id, updated = NOW() WHERE id = :id',
            $params, $types
        );
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
     * Remove one user and related data from storage
     *
     * @param int $user_id User identifier
     * 
     * @return bool
     */
    private static function _delete($user_id)
    {
        $conn = static::getDatabase();
        return $conn->executeQuery(
            'DELETE FROM user_detail WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM contact WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM user_addressbook WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM user_role WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM user WHERE id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->delete(static::USER_CACHE_KEY.'-'.$user_id);
    }

    /**
     * Toggle one user status
     * and flush user cache
     *
     * @param int  $user_id User identifier
     * @param bool $enabled Enables if true and disables if false
     * 
     * @return void
     */
    private function _toggleStatus($user_id, $enabled)
    {
        static::getDatabase()->executeQuery(
            'UPDATE user SET enabled = ? WHERE id = ?',
            [$enabled, $user_id],
            [
                \Doctrine\DBAL\ParameterType::BOOLEAN,
                \Doctrine\DBAL\ParameterType::INTEGER,
            ]
        );
        static::getDbCache()->delete(static::USER_CACHE_KEY.'-'.$user_id);
    }

    /**
     * Fetch one user from database (and cache it)
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
     * Fetch one user's details from database (and cache it)
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
            $details_query = 'SELECT detail.label, '.
                'AES_DECRYPT(value, UNHEX(SHA2(?, 512))) value '.
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

