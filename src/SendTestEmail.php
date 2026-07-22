<?php

namespace RH\AdminUtils;

use WP_CLI;
use WP_Error;

final class SendTestEmail
{
    /**
     * static init method. Registers the command
     */
    public static function init()
    {
        if (rhau()->is_wp_cli()) {
            WP_CLI::add_command('rhau send-test-email', self::class);
        }
    }


    /**
     * Sends a test email.
     *
     * ## OPTIONS
     *
     * [--to=<email>]
     * : The email address to send the test email to. Defaults to the site's admin email.
     *
     * [--subject=<string>]
     * : Overwrite the email subject
     *
     * [--body=<string>]
     * : Overwrite the email body
     *
     * ## EXAMPLES
     *
     *     wp rhau send-test-email
     *     wp rhau send-test-email --to=test@example.com
     *     wp rhau send-test-email --subject='does this work?'
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $to = $assoc_args['to'] ?? get_option('admin_email');

        $subject = $assoc_args['subject'] ?? 'Test Email';

        $body = $assoc_args['body'] ?? sprintf(
            'Sent from <a href="%s">%s</a>',
            home_url(),
            wp_parse_url(home_url(), PHP_URL_HOST),
        );

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        /** @var ?WP_Error $error */
        $error = null;

        $capture = function (WP_Error $e) use (&$error) {
            $error = $e;
        };

        add_action('wp_mail_failed', $capture);
        $success = wp_mail($to, $subject, $body, $headers);
        remove_action('wp_mail_failed', $capture);

        if ($success) {
            WP_CLI::success('Email sent');
            return;
        }

        WP_CLI::error('Error sending email: ' . ($error?->get_error_message() ?? 'unknown error'), false);

        if ($error) {
            WP_CLI::error_multi_line($error->get_error_messages());
        }

        exit(1);
    }
}
