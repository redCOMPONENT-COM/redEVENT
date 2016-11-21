<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4d05d2ea73c45f4488fbbcaae17b493b
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Redevent\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Redevent\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4d05d2ea73c45f4488fbbcaae17b493b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4d05d2ea73c45f4488fbbcaae17b493b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
