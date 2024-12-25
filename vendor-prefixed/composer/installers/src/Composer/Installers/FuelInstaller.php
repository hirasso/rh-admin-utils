<?php
/**
 * @license MIT
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Composer\Installers;

class FuelInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'module'  => 'fuel/app/modules/{$name}/',
        'package' => 'fuel/packages/{$name}/',
        'theme'   => 'fuel/app/themes/{$name}/',
    );
}
