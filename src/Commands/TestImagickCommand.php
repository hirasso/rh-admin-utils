<?php

namespace RH\AdminUtils\Commands;

use WP_CLI;

/**
 * Test if ImageMagick is available and which formats it can encode
 */
class TestImagickCommand
{
    /**
     * Tests which formats Imagick can actually encode. WP's supports_mime_type() also reports decode-only formats
     *
     * ## EXAMPLES
     *
     *     wp rhau test-imagick
     */
    public function __invoke(): void
    {
        \WP_CLI::log('SAPI: ' . PHP_SAPI);
        \WP_CLI::log('php.ini: ' . (php_ini_loaded_file() ?: 'none'));

        if (!self::is_imagick_available()) {
            \WP_CLI::error('WP can\'t use Imagick (extension missing, outdated or lacking required methods)');
        }

        \WP_CLI::log('Imagick: ' . phpversion('imagick'));
        \WP_CLI::log(\Imagick::getVersion()['versionString']);

        \WP_CLI::log('WP output formats: ' . implode(', ', self::get_output_formats()));

        $rows = self::get_format_rows();

        $assoc_args = ['format' => 'table', 'fields' => array_keys($rows[0])];
        $cells = array_map(fn ($row) => array_map(
            fn ($value) => is_bool($value) ? \WP_CLI::colorize($value ? '%G✔%n' : '%R✘%n') : $value,
            $row
        ), $rows);
        /** pre-colorized, else the table disables colors and miscounts widths */
        (new \WP_CLI\Formatter($assoc_args))->display_items($cells, true);

        if ($failed = self::get_unencodable_formats($rows)) {
            \WP_CLI::error('Imagick can\'t encode: ' . implode(', ', $failed));
        }

        \WP_CLI::success('Imagick can encode all formats WP generates');
    }

    /**
     * Check if Imagick is available
     */
    private static function is_imagick_available(): bool
    {
        require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
        require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

        return \WP_Image_Editor_Imagick::test();
    }

    /**
     * Get the formats WP saves resized images in
     */
    private static function get_output_formats(): array
    {
        $mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];

        return array_values(array_unique(array_map(
            fn ($mime_type) => wp_get_image_editor_output_format('', $mime_type)[$mime_type] ?? $mime_type,
            $mime_types
        )));
    }

    /**
     * Probe each WP output format. Requires Imagick to be available
     */
    private static function get_format_rows(): array
    {
        return array_map(fn ($mime_type) => [
            'format' => $mime_type,
            'Imagick claims support...' => \WP_Image_Editor_Imagick::supports_mime_type($mime_type),
            '...and can actually encode' => self::get_imagick_probe_bytes(wp_get_default_extension_for_mime_type($mime_type) ?: '') > 0,
        ], self::get_output_formats());
    }

    /**
     * Get all image formats that WP generates but Imagick can't encode
     */
    private static function get_unencodable_formats(array $rows): array
    {
        return array_column(array_filter(
            $rows,
            fn ($row) => !$row['...and can actually encode']
        ), 'format');
    }

    /**
     * Encodes an 8x8 image, returns the byte count (0 = can't encode)
     */
    private static function get_imagick_probe_bytes(string $extension): int
    {
        try {
            $image = new \Imagick();
            $image->newImage(8, 8, 'red');
            $image->setImageFormat($extension);
            return strlen($image->getImageBlob());
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Why WP would fall back to GD, null if Imagick works
     */
    private static function get_imagick_problem(): ?string
    {
        if (!self::is_imagick_available()) {
            return 'Imagick not available';
        }

        if ($unencodable = self::get_unencodable_formats(self::get_format_rows())) {
            return 'Imagick can\'t encode ' . implode(', ', $unencodable);
        }

        return null;
    }

    /**
     * Guard `wp media regenerate` against falling back to GD: prompt if interactive, error otherwise
     */
    public static function hook_into_media_regenerate(): void
    {
        WP_CLI::add_hook('before_invoke:media regenerate', function () {
            $problem = self::get_imagick_problem();
            if (!$problem) {
                return;
            }

            $message = "$problem, WP would fall back to GD";
            $hint = 'Check `wp rhau test-imagick`';

            if (!stream_isatty(STDIN)) {
                WP_CLI::error("$message. $hint");
            }

            WP_CLI::warning("$message. GD strips color profiles (colors may shift) and doesn't sharpen (resized images might look blurry). $hint");
            WP_CLI::confirm('Continue with GD anyway?');
        });
    }
}
