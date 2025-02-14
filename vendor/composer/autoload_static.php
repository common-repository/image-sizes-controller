<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit79793078db58d8541b2b2f4cfd34d5ea
{
    public static $files = array (
        '7e87d9513a86fac013533043178c505f' => __DIR__ . '/../..' . '/pages/pagesList.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\Core\\Core' => __DIR__ . '/../..' . '/includes/Core/Core.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\ImageSizes' => __DIR__ . '/../..' . '/includes/ImageSizes.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\ImageSubsizes' => __DIR__ . '/../..' . '/includes/ImageSubsizes.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\Utils' => __DIR__ . '/../..' . '/includes/Utils.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\Utils\\Helpers' => __DIR__ . '/../..' . '/utils/Helpers.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\Utils\\NoticeUtils' => __DIR__ . '/../..' . '/utils/NoticeUtils.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\modules\\SelectImages\\SelectImagesModule' => __DIR__ . '/../..' . '/modules/SelectImages/SelectImages.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\pages\\AdminPage' => __DIR__ . '/../..' . '/pages/AdminPage.php',
        'GPLSCore\\GPLS_PLUGIN_ISSL\\pages\\SizesControllerMainPage' => __DIR__ . '/../..' . '/pages/SizesControllerMainPage.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit79793078db58d8541b2b2f4cfd34d5ea::$classMap;

        }, null, ClassLoader::class);
    }
}
