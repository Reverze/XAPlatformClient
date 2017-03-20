<?php

namespace XA\PlatformClient\Routing;

class RouteDefinitions
{
    private static $routes = [
        'users.getUserObjectByUserName' => [
            'method' => 'get',
            'path' => '/users/get-user-object-by-user-name',
            'auth' => false
        ],
        'users.signin' => [
            'method' => 'get',
            'path' => '/users/signin-user',
            'auth' => false
        ],
        'sessions.getSession' => [
            'method' => 'get',
            'path' => '/session/get-session-object',
            'auth' => false
        ],
        'users.tickSession' => [
            'method' => 'post',
            'path' => '/users/tick-session',
            'auth' => true
        ],
        'users.wakeupUserById' => [
            'method' => 'get',
            'path' => '/users/wakeup-user-by-user-id',
            'auth' => true
        ],
        'users.createUser' => [
            'method' => 'put',
            'path' => '/users/create-user',
            'auth' => false
        ],
        'users.env.getCredParams' => [
            'method' => 'get',
            'path' => '/users/env-get-cred-params',
            'auth' => false
        ],
        'sessions.dropSingleSession' => [
            'method' => 'delete',
            'path' => '/session/drop-single-session',
            'auth' => true
        ],
        'users.isUsernameExists' => [
            'method' => 'get',
            'path' => '/users/is-username-exists',
            'auth' => false
        ],
        'users.isEmailAddressExists' => [
            'method' => 'get',
            'path' => '/users/is-email-address-exists',
            'auth' => false
        ],
        'users.isUserExistsByUserId' => [
            'method' => 'get',
            'path' => '/users/is-user-exists-by-userid',
            'auth' => false
        ],
        'users.getUserObjectByUserId' => [
            'method' => 'get',
            'path' => '/users/get-user-object-by-user-id',
            'auth' => false
        ],
        'users.getUserObjectByUserName' => [
            'method' => 'get',
            'path' => '/users/get-user-object-by-user-name',
            'auth' => false
        ],
        'users.getUserObjectByEmailAddress' => [
            'method' => 'get',
            'path' => '/users/get-user-object-by-email-address',
            'auth' => false
        ],
        'users.updateUserName' => [
            'method' => 'post',
            'path' => '/users/update-user-name',
            'auth' => true
        ],
        'users.updateEmailAddress' => [
            'method' => 'post',
            'path' => '/users/update-email-address',
            'auth' => true
        ],
        'authcred.beginPasswordChange' => [
            'method' => 'post',
            'path' => '/auth-cred/begin-password-change',
            'auth' => true
        ],
        'authcred.finishPasswordChange' => [
            'method' => 'post',
            'path' => '/auth-cred/finish-password-change',
            'auth' => false
        ],
        'users.confirmAccount'=> [
            'method' => 'post',
            'path' => '/users/confirm-user-account',
            'auth' => true
        ],
        'users.beginChangeNameProcess' => [
            'method' => 'post',
            'path' => '/users/begin-username-change-process',
            'auth' => true
        ],
        'users.finishChangeNameProcess' => [
            'method' => 'post',
            'path' => '/users/finish-username-change-process',
            'auth' => true
        ],
        'users.askEmailChange' => [
            'method' => 'post',
            'path' => '/users/ask-email-change',
            'auth' => true
        ],
        'users.acceptAskEmailChange' => [
            'method' => 'post',
            'path' => '/users/accept-ask-email-change',
            'auth' => false
        ],
        'users.finishEmailChange' => [
            'method' => 'post',
            'path' => '/users/finish-email-change',
            'auth' => false
        ],
        'users.verifyUserPassword' => [
            'method' => 'get',
            'path' => '/users/verify-user-password',
            'auth' => true
        ],
        'users.updateUserAvatarUri' => [
            'method' => 'post',
            'path' => '/users/update-user-avatar',
            'auth' => true
        ],
        'users.dropUserAvatar' => [
            'method' => 'delete',
            'path' => '/users/drop-user-avatar',
            'auth' => true
        ]
    ];


    /**
     * Gets all routes definitions
     * @return array
     */
    public static function getRouteDefinitions() : array
    {
        return self::$routes;
    }

    /**
     * Gets route parameter
     * @param string $routeName
     * @return array|null Returns array with route's parameters, if route was not found return null.
     */
    public static function getRoute(string $routeName)
    {
        if (array_key_exists($routeName, self::$routes)){
            return self::$routes[$routeName];
        }

        return null;
    }
}

?>