<?php
/**
 * @license MIT
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Composer\Installers;

class CroogoInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'plugin' => 'Plugin/{$name}/',
        'theme' => 'View/Themed/{$name}/',
    );

    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars(array $vars): array
    {
        $vars['name'] = strtolower(str_replace(array('-', '_'), ' ', $vars['name']));
        $vars['name'] = str_replace(' ', '', ucwords($vars['name']));

        return $vars;
    }
}
