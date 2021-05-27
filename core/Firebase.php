<?php

namespace App;

use Kreait\Firebase\Factory;

class Firebase
{
    private static $factory;

    function __construct()
    {
        if (!empty($_ENV['FIREBASE_DATABASE_URL'])) {
            self::$factory = (new Factory)->withServiceAccount(__DIR__ . '/../' . $_ENV['FIREBASE_CREDENTIALS'])
                ->withDatabaseUri($_ENV['FIREBASE_DATABASE_URL']);
        } else {
            self::$factory = (new Factory)->withServiceAccount(__DIR__ . '/../' . $_ENV['FIREBASE_CREDENTIALS']);
        }
    }

    public static function auth()
    {
        return self::$factory->createAuth();
    }

    public static function firestore()
    {
        return self::$factory->createFirestore();
    }

    public static function messaging()
    {
        return self::$factory->createMessaging();
    }

    public static function dynamicLinks(string $dynamicLinksDomain)
    {
        return self::$factory->createDynamicLinksService($dynamicLinksDomain);
    }

    public static function database()
    {
        return self::$factory->createDatabase();
    }

    public static function remoteConfig()
    {
        return self::$factory->createRemoteConfig();
    }

    public static function storage()
    {
        return self::$factory->createStorage();
    }
}
