<?php

declare(strict_types=1);

namespace RH\AdminUtils;

class HelperPluginInstaller
{
    public const VERSION = '1.0.0';
    private string $muPluginsDir = WP_CONTENT_DIR . '/mu-plugins';
    private string $fileName = 'rh-admin-utils-helper.php';
    private string $rootDir;

    public function __construct(string $mainPluginFile)
    {
        $this->rootDir = rtrim(plugin_dir_path($mainPluginFile), '/');
        $this->install();
        register_deactivation_hook($mainPluginFile, [$this, 'uninstall']);
    }

    /**
     * Install the mu plugin
     */
    private function install()
    {
        $sourceFile = "$this->rootDir/$this->fileName";
        $destinationFile = "$this->muPluginsDir/$this->fileName";
        $storedVersion = get_option('rhau_mu_plugin_version');

        if ($storedVersion === static::VERSION && file_exists($destinationFile)) {
            return;
        }

        if (!is_dir($this->muPluginsDir)) {
            mkdir($this->muPluginsDir, 0755, true);
        }

        /** Always reinstall the mu-plugin on activation */
        static::uninstall();

        copy($sourceFile, $destinationFile);

        update_option('rhau_mu_plugin_version', static::VERSION, true);
    }

    /**
     * Uninstall the mu plugin
     */
    public function uninstall()
    {
        $file = "$this->muPluginsDir/$this->fileName";

        if (file_exists($file)) {
            unlink($file);
        }
    }
}
