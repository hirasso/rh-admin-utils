<?php
/**
 * @license MIT
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Composer\Installers;

class PhpBBInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'extension' => 'ext/{$vendor}/{$name}/',
        'language'  => 'language/{$name}/',
        'style'     => 'styles/{$name}/',
    );
}
