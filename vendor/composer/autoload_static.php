<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit78520cb9f1103f908441ebb2b8daafed
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Basilicom\\AreabrickReport\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Basilicom\\AreabrickReport\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit78520cb9f1103f908441ebb2b8daafed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit78520cb9f1103f908441ebb2b8daafed::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit78520cb9f1103f908441ebb2b8daafed::$classMap;

        }, null, ClassLoader::class);
    }
}
