<?php

namespace RH\AdminUtils\YahnisElsts\PluginUpdateChecker\v5p5;

use RH\AdminUtils\YahnisElsts\PluginUpdateChecker\v5\PucFactory as MajorFactory;
use RH\AdminUtils\YahnisElsts\PluginUpdateChecker\v5p5\PucFactory as MinorFactory;
require __DIR__ . '/Puc/v5p5/Autoloader.php';
new Autoloader();
require __DIR__ . '/Puc/v5p5/PucFactory.php';
require __DIR__ . '/Puc/v5/PucFactory.php';
//Register classes defined in this version with the factory.
foreach (array('RH\AdminUtils\Plugin\UpdateChecker' => Plugin\UpdateChecker::class, 'RH\AdminUtils\Theme\UpdateChecker' => Theme\UpdateChecker::class, 'RH\AdminUtils\Vcs\PluginUpdateChecker' => Vcs\PluginUpdateChecker::class, 'RH\AdminUtils\Vcs\ThemeUpdateChecker' => Vcs\ThemeUpdateChecker::class, 'GitHubApi' => Vcs\GitHubApi::class, 'BitBucketApi' => Vcs\BitBucketApi::class, 'GitLabApi' => Vcs\GitLabApi::class) as $pucGeneralClass => $pucVersionedClass) {
    MajorFactory::addVersion($pucGeneralClass, $pucVersionedClass, '5.5');
    //Also add it to the minor-version factory in case the major-version factory
    //was already defined by another, older version of the update checker.
    MinorFactory::addVersion($pucGeneralClass, $pucVersionedClass, '5.5');
}
