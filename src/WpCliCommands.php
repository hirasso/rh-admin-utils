<?php

namespace RH\AdminUtils;

/**
 * Various handy WP CLI commands
 */
class WpCliCommands
{
    /**
     * Static init function instead of a constructor
     */
    public static function init()
    {
        if (!rhau()->is_wp_cli()) {
            return;
        }
        \WP_CLI::add_command('rhau do-action-save-post', [__CLASS__, 'wp_cli_do_action_save_post']);
    }

    /**
     * Runs the `save_post` action on posts
     *
     * ## OPTIONS
     *
     * [--type=<string>]
     * : A comma-separated list of post types to be processed
     *
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public static function wp_cli_do_action_save_post(array $args = [], array $assoc_args = []): void
    {
        $post_type = $assoc_args['type'] ?? null;
        if (!$post_type) {
            \WP_CLI::error("Please provide a post type");
        }
        if (!post_type_exists($post_type)) {
            \WP_CLI::error("Post type $post_type does not exist");
        }
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ]);
        $count = count($posts);
        $pt_object = get_post_type_object($post_type);
        \WP_CLI::log("Processing $count posts from {$pt_object->labels->name}");

        foreach ($posts as $post) {
            do_action('save_post', $post->ID, $post, true);
        }
    }
}
