<?php

namespace RH\AdminUtils\SimplyStatic;

use Simply_Static\Options;
use Simply_Static\Plugin;
use Simply_Static\Util;
use WP_CLI;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI commands for Simply Static
 */
class CLI
{
    /**
     * Generate a static site export as a ZIP file.
     *
     * ## OPTIONS
     *
     * [--output=<path>]
     * : Custom output path for the ZIP file. Defaults to current working directory.
     *
     * [--filename=<name>]
     * : Custom filename for the ZIP file (without .zip extension). Defaults to auto-generated name.
     *
     * ## EXAMPLES
     *
     *     # Generate static site in current directory
     *     $ wp simply-static run
     *
     *     # Generate with custom output path
     *     $ wp simply-static run --output=/path/to/output
     *
     *     # Generate with custom filename
     *     $ wp simply-static run --filename=my-static-site
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Named arguments.
     */
    public function run($args, $assoc_args)
    {
        WP_CLI::log('Starting Simply Static export...');

        // Get output directory (default to current working directory)
        $output_dir = isset($assoc_args['output']) ? $assoc_args['output'] : getcwd();
        $output_dir = trailingslashit($output_dir);

        // Verify output directory is writable
        if (! is_dir($output_dir)) {
            WP_CLI::error("Output directory does not exist: {$output_dir}");
        }

        if (! is_writable($output_dir)) {
            WP_CLI::error("Output directory is not writable: {$output_dir}");
        }

        // Get options and ensure delivery method is set to zip
        $options = Options::instance();
        $original_delivery_method = $options->get('delivery_method');

        // Temporarily set delivery method to zip
        $options->set('delivery_method', 'zip')->save();

        // Get blog ID
        $blog_id = get_current_blog_id();

        // Get archive creation job
        $job = Plugin::instance()->get_archive_creation_job();

        // Check if a job is already running
        if (! $job->is_job_done()) {
            WP_CLI::error('An export is already running. Please wait for it to complete or cancel it first.');
        }

        // Start the export
        WP_CLI::log('Initializing export...');
        if (! Plugin::instance()->run_static_export($blog_id, 'export')) {
            WP_CLI::error('Failed to start export. Check Simply Static settings and logs.');
        }

        // Poll for completion
        WP_CLI::log('Export in progress...');
        $max_wait = 3600; // 1 hour maximum
        $elapsed = 0;
        $check_interval = 2; // seconds
        $last_task = '';

        // Use reflection to access the protected handle() method for synchronous processing
        $reflection = new \ReflectionClass($job);
        $handle_method = $reflection->getMethod('handle');

        while (! $job->is_job_done() && $elapsed < $max_wait) {
            // Check if there's work to do and no lock
            if ($job->is_queued() && ! $job->is_processing()) {
                // Process the queue synchronously instead of using async dispatch
                try {
                    $handle_method->invoke($job);
                } catch (\Exception $e) {
                    WP_CLI::error("Error processing queue: {$e->getMessage()}");
                }
            }

            // Small delay to prevent tight loop
            sleep($check_interval);
            $elapsed += $check_interval;

            // Show progress when task changes or every 10 seconds
            $current_task = $job->get_current_task();
            if ($current_task !== $last_task || ($elapsed % 10 === 0)) {
                if (! empty($current_task)) {
                    WP_CLI::log("Processing task: {$current_task}");
                }
                $last_task = $current_task;
            }
        }

        // Restore original delivery method
        $options->set('delivery_method', $original_delivery_method)->save();

        // Check if job completed successfully
        if (! $job->is_job_done()) {
            WP_CLI::error('Export timed out after ' . $max_wait . ' seconds.');
        }

        // Check for errors in status messages
        $status_messages = $options->get('archive_status_messages');
        if (is_array($status_messages)) {
            foreach ($status_messages as $key => $message) {
                if ($key === 'error' || strpos(strtolower($message), 'error') !== false) {
                    WP_CLI::error("Export failed: {$message}");
                }
            }
        }

        // Find the generated ZIP file
        $temp_dir = Util::get_temp_dir();
        $archive_name = $options->get('archive_name');

        if (empty($archive_name)) {
            WP_CLI::error('Could not determine archive name. Export may have failed.');
        }

        $zip_filename = untrailingslashit($temp_dir . $archive_name) . '.zip';

        if (! file_exists($zip_filename)) {
            WP_CLI::error("ZIP file not found at expected location: {$zip_filename}");
        }

        // Determine destination filename
        if (isset($assoc_args['filename'])) {
            $destination_filename = sanitize_file_name($assoc_args['filename']);
            if (! preg_match('/\.zip$/i', $destination_filename)) {
                $destination_filename .= '.zip';
            }
        } else {
            $destination_filename = basename($zip_filename);
        }

        $destination_path = $output_dir . $destination_filename;

        // Copy the ZIP file to the destination
        WP_CLI::log("Copying ZIP file to: {$destination_path}");

        if (! copy($zip_filename, $destination_path)) {
            WP_CLI::error("Failed to copy ZIP file to destination: {$destination_path}");
        }

        // Get file size for reporting
        $file_size = size_format(filesize($destination_path));

        WP_CLI::success("Static site exported successfully!");
        WP_CLI::log("Location: {$destination_path}");
        WP_CLI::log("Size: {$file_size}");
    }

    /**
     * Cancel a running export.
     *
     * ## EXAMPLES
     *
     *     $ wp simply-static cancel
     *
     */
    public function cancel()
    {
        $job = Plugin::instance()->get_archive_creation_job();

        if ($job->is_job_done()) {
            WP_CLI::warning('No export is currently running.');
            return;
        }

        WP_CLI::log('Cancelling export...');
        Plugin::instance()->cancel_static_export();

        WP_CLI::success('Export cancelled.');
    }

    /**
     * Get the status of the current export.
     *
     * ## EXAMPLES
     *
     *     $ wp simply-static status
     *
     */
    public function status()
    {
        $job = Plugin::instance()->get_archive_creation_job();
        $options = Options::instance();

        if ($job->is_job_done()) {
            $start_time = $options->get('archive_start_time');
            $end_time = $options->get('archive_end_time');

            if ($start_time && $end_time) {
                WP_CLI::log('Status: Completed');
                WP_CLI::log("Started: {$start_time}");
                WP_CLI::log("Ended: {$end_time}");
            } else {
                WP_CLI::log('Status: No export has been run yet');
            }
        } else {
            $current_task = $job->get_current_task();
            $start_time = $options->get('archive_start_time');

            WP_CLI::log('Status: Running');
            WP_CLI::log("Current task: {$current_task}");
            WP_CLI::log("Started: {$start_time}");
        }

        // Show recent status messages
        $status_messages = $options->get('archive_status_messages');
        if (is_array($status_messages) && ! empty($status_messages)) {
            WP_CLI::log("\nRecent messages:");
            $recent_messages = array_slice($status_messages, -5, 5, true);
            foreach ($recent_messages as $key => $message) {
                // Handle array values
                if (is_array($message)) {
                    $message = ! empty($message) ? implode(', ', array_filter($message)) : 'Processing...';
                }
                WP_CLI::log("  [{$key}] {$message}");
            }
        }
    }
}
