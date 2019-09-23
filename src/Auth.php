<?php
/**
 * Auth class file
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
use ReallySimpleJWT\Token as Token;

/**
 * Auth class
 *
 * Auth model
 *
 * @category Model
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
final class Auth
{
    use Database;

    /**
     * Add a wallet item
     *
     * @param int    $user_id   User identifier
     * @param int    $wallet_id Type of wallet item
     * @param string $value     Value to store
     *
     * @return int
     */
    public static function post($user_id, $wallet_id, $value)
    {
        $db = static::getDatabase();
        $db->executeQuery(
            'INSERT INTO user_wallet (user_id, wallet_id, value, created) '.
            'VALUES (?, ?, AES_ENCRYPT(?, UNHEX(SHA2(?, 512))), NOW())',
            [
                $user_id,
                $wallet_id,
                $value,
                AUTH_SECRET
            ],
            [
                \Doctrine\DBAL\ParameterType::INTEGER,
                \Doctrine\DBAL\ParameterType::INTEGER,
                \Doctrine\DBAL\ParameterType::BINARY,
                \Doctrine\DBAL\ParameterType::STRING
            ]
        );

        return (int) $db->lastInsertId();
    }

    /**
     * Returns a new JWT token
     *
     * @param int $user_id User identifier
     *
     * @return string Token
     */
    public static function get($user_id)
    {
        $token = Token::create(
            $user_id,
            TOKEN_SECRET,
            time() + 3600,
            $_SERVER['SERVER_NAME']
        );
        echo json_encode([
            'header' => Token::getHeader($token, TOKEN_SECRET),
            'payload' => Token::getPayload($token, TOKEN_SECRET),
        ]);
        //$result = Token::validate($token, TOKEN_SECRET);

        return $token;
    }
}
