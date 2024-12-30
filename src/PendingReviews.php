<?php

namespace RH\AdminUtils;

class PendingReviews extends Singleton
{
    public function __construct()
    {
        add_action('save_post', [$this, 'save_post']);
        add_action('acf/init', [$this, 'acf_init']);
        add_filter('acf/location/rule_match', [$this, 'acf_location_rule_match'], 10, 4);
        add_action('admin_menu', [$this, 'admin_menu_add_pending_badges']);
    }

    /**
     * Adds a checkbox to user edit forms to be notified about pending reviews
     *
     * @return void
     */
    public function acf_init()
    {
        acf_add_local_field_group([
            'key' => 'group_rhau_pending_reviews_user_form',
            'title' => 'Pending Reviews',
            'fields' => [
                [
                    'key' => 'field_key_subscribe_to_pending_reviews',
                    'label' => 'Subscribe to pending reviews',
                    'name' => 'subscribe_to_pending_reviews',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'hide_in' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'rhau_user_form_capability',
                        'operator' => '==',
                        'value' => 'edit_others_posts',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'active' => true,
            'show_in_rest' => 0,
        ]);
    }

    /**
     * Undocumented function
     *
     * @param boolean $match
     * @param array $rule
     * @param array $screen
     * @param array $field_group
     * @return boolean
     */
    public function acf_location_rule_match(bool $match, array $rule, array $screen, array $field_group): bool
    {

        $rule_param = $rule['param'] ?? null;
        if ($rule_param !== 'rhau_user_form_capability') {
            return $match;
        }

        $cap = $rule['value'] ?? null;
        if (!$cap) {
            return $match;
        }

        $user_form = $screen['user_form'] ?? null;
        if ($user_form !== 'edit') {
            return $match;
        }

        global $user_id;
        if (!$user = get_userdata($user_id)) {
            return $match;
        }

        return $user->has_cap($cap);
    }

    /**
     * Sends an email notification each time someone submits a post for review
     *
     * @param integer $post_id
     * @return void
     */
    public function save_post(int $post_id): void
    {

        $post = get_post($post_id);
        if (in_array($post->post_type, ['revision'])) {
            return;
        }
        if (!in_array($post->post_status, ['pending'])) {
            return;
        }
        if (!is_post_type_viewable($post->post_type)) {
            return;
        }
        if (wp_doing_ajax()) {
            return;
        }
        if (!is_user_logged_in()) {
            return;
        }

        if (!apply_filters('rhau/send_pending_review_notifications', true)) {
            return;
        }
        if (!apply_filters("rhau/send_pending_review_notifications/post_type=$post->post_type", true)) {
            return;
        }

        // Only send notifications each 5 minutes
        $transient_name = "block_pending_review_notification_for_{$post_id}";
        // delete_transient($transient_name);
        if (get_transient($transient_name)) {
            return;
        }
        set_transient($transient_name, true, MINUTE_IN_SECONDS * 5);

        $mail_success = $this->send_pending_review_notifiation($post_id);
    }

    /**
     * Filter the content type for wp_mail
     *
     * @param string $content_type
     * @return string
     */
    public function wp_mail_content_type(string $content_type): string
    {
        return "text/html";
    }

    /**
     * Send a notification email each time a post is submitted for review
     *
     * @param integer $post_id
     * @return boolean
     */
    private function send_pending_review_notifiation(int $post_id): bool
    {
        $edit_link = get_edit_post_link($post_id);

        add_filter('wp_mail_content_type', [$this, 'wp_mail_content_type']);

        $emails = $this->get_subscriber_emails();
        if (empty($emails)) {
            return false;
        }

        $user = wp_get_current_user();
        $post_title = get_the_title($post_id);

        $result = wp_mail(
            implode(',', $emails),
            'New pending review',
            "<p>$user->display_name just submitted a post for review.</p> <p>Title: $post_title  <br>Edit Link: $edit_link"
        );

        remove_filter('wp_mail_content_type', [$this, 'wp_mail_content_type']);

        return $result;
    }

    /**
     * Get users emails for all users that are subsribed to pending reviews
     *
     * @return array
     */
    private function get_subscriber_emails(): array
    {
        $subscribers = [];
        $users = get_users([
            'meta_key' => 'subscribe_to_pending_reviews',
            'meta_value' => '1',
            'capability' => 'edit_others_posts',
        ]);
        foreach ($users as $user) {
            $subscribers[] = $user->data->user_email;
        }
        return $subscribers;
    }

    /**
     * Renders a 'pending' badge in the admin_menu for all post types
     * that support 'rhau-pending-badge'
     *
     * @return void
     */
    public function admin_menu_add_pending_badges(): void
    {
        global $menu;
        /**
         * Only show the badge to users that can actually
         * edit and approve pending posts
         */
        if (!current_user_can('edit_others_pages')) {
            return;
        }

        $post_types = get_post_types_by_support('rhau-pending-badge');

        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type);

            if (empty($count->pending)) {
                continue;
            }

            foreach ($menu as &$menu_item) {
                if ($menu_item[2] === "edit.php?post_type=$post_type") {
                    $menu_item[0] .= " <span class='awaiting-mod' title='{$count->pending} pending reviews'><span>{$count->pending}</span></span>";
                }
                unset($menu_item);
            }
        }
    }
}
