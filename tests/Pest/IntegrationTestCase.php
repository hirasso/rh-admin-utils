<?php

declare(strict_types=1);

namespace RH\AdminUtils\Tests\Pest;

/**
 * Patches WP_UnitTestCase for PHPUnit 11: wp-phpunit's expectDeprecated() still calls
 * PHPUnit\Util\Test::parseTestMethodAnnotations(), removed in 11. Keeps the hooks,
 * drops the annotation parsing, which we don't use.
 */
class IntegrationTestCase extends \WP_UnitTestCase
{
    public function expectDeprecated(): void
    {
        add_action('deprecated_function_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_argument_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_class_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_file_included', [$this, 'deprecated_function_run'], 10, 4);
        add_action('deprecated_hook_run', [$this, 'deprecated_function_run'], 10, 4);
        add_action('doing_it_wrong_run', [$this, 'doing_it_wrong_run'], 10, 3);

        add_filter('deprecated_function_trigger_error', '__return_false');
        add_filter('deprecated_argument_trigger_error', '__return_false');
        add_filter('deprecated_class_trigger_error', '__return_false');
        add_filter('deprecated_file_trigger_error', '__return_false');
        add_filter('deprecated_hook_trigger_error', '__return_false');
        add_filter('doing_it_wrong_trigger_error', '__return_false');
    }
}
