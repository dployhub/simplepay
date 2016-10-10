<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit16e18bd04140e87ee6af44542095848e
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dploy\\Simplepay\\Tests\\' => 22,
            'Dploy\\Simplepay\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dploy\\Simplepay\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'Dploy\\Simplepay\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit16e18bd04140e87ee6af44542095848e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit16e18bd04140e87ee6af44542095848e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}