<?php
/**
 * @license MIT
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Composer\Installers;

class PlentymarketsInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'plugin'   => '{$name}/'
    );

    /**
     * Remove hyphen, "plugin" and format to camelcase
     */
    public function inflectPackageVars(array $vars): array
    {
        $nameBits = explode("-", $vars['name']);
        foreach ($nameBits as $key => $name) {
            $nameBits[$key] = ucfirst($name);
            if (strcasecmp($name, "Plugin") == 0) {
                unset($nameBits[$key]);
            }
        }
        $vars['name'] = implode('', $nameBits);

        return $vars;
    }
}
