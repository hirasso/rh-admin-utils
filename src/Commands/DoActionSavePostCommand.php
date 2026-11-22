<?php

namespace RH\AdminUtils\Commands;

use WP_CLI;

/**
 * Various handy WP CLI commands
 */
class DoActionSavePostCommand
{
    /**
     * Runs the `save_post` action on posts
     *
     * ## OPTIONS
     *
     * [--type=<string>]
     * : A comma-separated list of post types to be processed
     */
    public function __invoke(array $args = [], array $assoc_args = []): void
    {
        $post_type = $assoc_args['type'] ?? null;
        if (!$post_type) {
            WP_CLI::error("Please provide a post type");
        }
        if (!post_type_exists($post_type)) {
            WP_CLI::error("Post type $post_type does not exist");
        }
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ]);
        $count = count($posts);
        $pt_object = get_post_type_object($post_type);

        WP_CLI::log("Processing $count posts from {$pt_object->labels->name}");

        foreach ($posts as $post) {
            do_action('save_post', $post->ID, $post, true);
        }
    }
}
