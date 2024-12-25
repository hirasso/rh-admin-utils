<?php
/**
 * @license MIT
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Composer\Installers;

class ReIndexInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'theme'     => 'themes/{$name}/',
        'plugin'    => 'plugins/{$name}/'
    );
}
