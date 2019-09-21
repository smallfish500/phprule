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
final class Users
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
     * @return bool
     */
    public static function one($user_id)
    {
        if (!static::can('user_display')) {
            return false;
        }
        $user = static::_fetchUser($user_id);
        static::show(['users' => [$user]]);

        return true;
    }

    /**
     * Get the connected user and the details
     *
     * @return bool
     */
    public static function me()
    {
        $my_id = 1; // XXX authenticated user
        $user = static::_fetchUser($my_id);
        $details = static::_fetchDetails($my_id);

        static::show(['user' => $user, 'details' => $details], 'user_details.html');

        return true;
    }

    /**
     * Get one user with the details
     *
     * @param int $user_id User identifier
     * 
     * @return bool
     */
    public static function details($user_id)
    {
        $user = static::_fetchUser($user_id);
        $details = static::_fetchDetails($user_id);
        static::show(['user' => $user, 'details' => $details], 'user_details.html');

        return true;
    }

    /**
     * Returns the addressbooks list for a given $user_id
     *
     * @param int $user_id User identifier
     *
     * @return int
     * 
     * @todo return addressbooks using cache
     */
    public static function addressbooks($user_id)
    {
        return $user_id;
    }

    /**
     * Displays contacts of $addressbook_id
     *
     * @param int $addressbook_id Addressbook identifier
     *
     * @return bool
     */
    public static function addressbook($addressbook_id)
    {
        $user_id = 1; // XXX  authenticated user
        if (!static::_allowedAddressbook($user_id, $addressbook_id)) {
            return false;
        }

        static::show(['users' => static::_getAddressbookContacts($addressbook_id)]);

        return true;
    }

    /**
     * Returns contacts in $addressbook_id
     *
     * @param int $addressbook_id Addressbook identifier
     *
     * @return array
     *
     * @todo add cache
     */
    private static function _getAddressbookContacts($addressbook_id)
    {
        return static::getDatabase()->executeQuery(
            'SELECT u.*, '.
            'AES_DECRYPT(u.password, UNHEX(SHA2(:secret, 512))) password '.
            'FROM user u '.
            'INNER JOIN contact c ON u.id = c.user_id '.
            'WHERE c.addressbook_id = :id',
            ['secret' => PASS_SECRET, 'id' => $addressbook_id],
            [
                'secret' => \Doctrine\DBAL\ParameterType::STRING,
                'id' => \Doctrine\DBAL\ParameterType::INTEGER
            ]
        )->fetchAll();
    }

    /**
     * Returns true if $user_id is allowed to access $addressbook_id
     *
     * @param int $user_id        User identifier
     * @param int $addressbook_id Addressbook identifier
     *
     * @return bool
     *
     * @todo add cache
     */
    private static function _allowedAddressbook($user_id, $addressbook_id)
    {
        return (bool) static::getDatabase()->executeQuery(
            'SELECT user_id FROM user_addressbook '.
            'WHERE addressbook_id = ? AND user_id = ?',
            [$addressbook_id, $user_id],
            [
                \Doctrine\DBAL\ParameterType::INTEGER,
                \Doctrine\DBAL\ParameterType::INTEGER
            ]
        )->fetch();
    }

    /**
     * Delete one user and related data
     *
     * @param int $user_id User identifier
     *
     * @return bool
     */
    public static function delete($user_id)
    {
        $user = static::_fetchUser($user_id);
        $success = static::_delete($user_id);
        static::show(
            ['user' => $user, 'message' => $success ? 'success' : 'failure'],
            'user_action.html'
        );

        return true;
    }

    /**
     * Create one user
     * 
     * @return int Created user identifier
     */
    public static function post()
    {
        $db = static::getDatabase();
        $db->executeQuery(
            'INSERT INTO user (label, password, enabled, create_user_id, created) '.
            'VALUES (?, AES_ENCRYPT(?, UNHEX(SHA2(?, 512))), 1, ?, NOW())',
            [
                $_POST['label'],
                $_POST['password'],
                PASS_SECRET,
                1, // XXX authenticated user
            ],
            [
                \Doctrine\DBAL\ParameterType::STRING,
                \Doctrine\DBAL\ParameterType::BINARY,
                \Doctrine\DBAL\ParameterType::STRING,
                \Doctrine\DBAL\ParameterType::INTEGER,
            ]
        );

        $user_id = (int) $db->lastInsertId();
        static::one($user_id);

        return $user_id;
    }

    /**
     * Modify one user
     *
     * @param int $user_id User identifier
     * 
     * @return bool
     */
    public static function modify($user_id)
    {
        parse_str(file_get_contents('php://input'), $_PATCH);
        $params = [
            'id' => $user_id,
            'update_user_id' => 1, // XXX authenticated user
        ];
        $types = [
            'id' => \Doctrine\DBAL\ParameterType::INTEGER,
            'update_user_id' => \Doctrine\DBAL\ParameterType::INTEGER,
        ];
        if (!empty($_PATCH['label'])) {
            $params += ['label' => $_PATCH['label']];
            $types += ['label' => \Doctrine\DBAL\ParameterType::STRING];
        }
        if (!empty($_PATCH['password'])) {
            $params += ['password' => $_PATCH['password']];
            $params += ['secret' => PASS_SECRET];
            $types += ['label' => \Doctrine\DBAL\ParameterType::BINARY];
            $types += ['secret' => \Doctrine\DBAL\ParameterType::STRING];
        }

        $query = 'UPDATE user SET '.
            (!empty($_PATCH['label']) ? 'label = :label, ' : '').
            (!empty($_PATCH['password']) ? 'password = '.
            'AES_ENCRYPT(:password, UNHEX(SHA2(:secret, 512))), ' : '').
            'update_user_id = :update_user_id, updated = NOW() WHERE id = :id';

        static::getDatabase()->executeQuery($query, $params, $types);

        return true;
    }

    /**
     * Enable one user
     *
     * @param int $user_id User identifier
     * 
     * @return bool
     */
    public static function enable($user_id)
    {
        static::_toggleStatus($user_id, true);

        return true;
    }

    /**
     * Disable one user
     *
     * @param int $user_id User identifier
     * 
     * @return bool
     */
    public static function disable($user_id)
    {
        static::_toggleStatus($user_id, false);

        return true;
    }

    /**
     * Returns true if $user_id has $privilege
     *
     * @param string $privilege Privilege label
     * @param int    $user_id   User identifier
     * 
     * @return boolean
     */
    public static function can($privilege, $user_id = 0)
    {
        if (!$user_id) {
            $user_id = 1; // XXX authenticated user
        }

        $found_privilege = static::getDatabase()->executeQuery(
            'SELECT * FROM user_role ur '.
            'INNER JOIN role_privilege rp ON rp.role_id = ur.role_id '.
            'INNER JOIN privilege p ON p.id = rp.privilege_id '.
            'AND p.label = ? AND p.enabled = 1 '.
            'WHERE ur.user_id = ?',
            [$privilege, $user_id],
            [
                \Doctrine\DBAL\ParameterType::STRING,
                \Doctrine\DBAL\ParameterType::INTEGER
            ]
        )->fetch();

        return (bool) $found_privilege;
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
        return static::_deleteUserDetailsAndRoles($user_id)
        && static::_deleteContactsAndAddressbooks($user_id)
        && static::getDatabase()->executeQuery(
            'DELETE FROM user WHERE id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && static::getDbCache()->delete(static::USER_CACHE_KEY.'-'.$user_id);
    }

    /**
     * Deletes details and roles for a given user
     *
     * @param int $user_id User identifier
     *
     * @return bool
     */
    private static function _deleteUserDetailsAndRoles($user_id)
    {
        $conn = static::getDatabase();
        return $conn->executeQuery(
            'DELETE FROM user_detail WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM user_role WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        );
    }

    /**
     * Deletes contacts and addressbooks for a given user
     *
     * @param int $user_id User identifier
     *
     * @return bool
     */
    private static function _deleteContactsAndAddressbooks($user_id)
    {
        $conn = static::getDatabase();
        return $conn->executeQuery(
            'DELETE FROM contact WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        ) && $conn->executeQuery(
            'DELETE FROM user_addressbook WHERE user_id = ?',
            [$user_id],
            [\Doctrine\DBAL\ParameterType::INTEGER]
        );
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
    private static function _toggleStatus($user_id, $enabled)
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
                'SELECT *, AES_DECRYPT(password, UNHEX(SHA2(?, 512))) password '.
                'FROM user WHERE id = ? AND enabled = 1',
                [PASS_SECRET, $user_id],
                [
                    \Doctrine\DBAL\ParameterType::STRING,
                    \Doctrine\DBAL\ParameterType::INTEGER
                ]
            )->fetch();
            $cache->save($key, $user);
        }

        return (array) $user;
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
                'FROM user_detail '.
                'INNER JOIN detail ON detail.id = user_detail.detail_id '.
                'WHERE user_id = ?';
            $details = static::getDatabase()->executeQuery(
                $details_query,
                [USER_SECRET, $user_id],
                [
                    \Doctrine\DBAL\ParameterType::STRING,
                    \Doctrine\DBAL\ParameterType::INTEGER
                ]
            )->fetchAll();
            $cache->save($key, $details);
        }

        return (array) $details;
    }
}
