<?php 

namespace RH\AdminUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PendingReviews extends Singleton {

  public function __construct() {
    add_action('save_post', [$this, 'save_post']);
    add_action('acf/init', [$this, 'acf_init']);
  }

  /**
   * Adds a checkbox to user edit forms to be notified about pending reviews
   *
   * @return void
   */
  public function acf_init() {
    acf_add_local_field_group(array(
      'key' => 'group_rhau_pending_reviews_user_form',
      'title' => 'Pending Reviews',
      'fields' => array(
        array(
          'key' => 'field_key_subscribe_to_pending_reviews',
          'label' => 'Subscribe to pending reviews',
          'name' => 'subscribe_to_pending_reviews',
          'type' => 'true_false',
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'hide_in' => '',
          'message' => '',
          'default_value' => 0,
          'ui' => 1,
          'ui_on_text' => '',
          'ui_off_text' => '',
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'user_form',
            'operator' => '==',
            'value' => 'edit',
          ),
          array(
            'param' => 'user_role',
            'operator' => '==',
            'value' => 'administrator',
          ),
        ),
        array(
          array(
            'param' => 'user_form',
            'operator' => '==',
            'value' => 'edit',
          ),
          array(
            'param' => 'user_role',
            'operator' => '==',
            'value' => 'editor',
          ),
        ),
        array(
          array(
            'param' => 'user_form',
            'operator' => '==',
            'value' => 'edit',
          ),
          array(
            'param' => 'user_role',
            'operator' => '==',
            'value' => 'editor_in_chief',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'acf_after_title',
      'active' => true,
      'description' => '',
      'show_in_rest' => 0,
    ));
  }

  /**
   * Sends an email notification each time someone submits a post for review
   *
   * @param integer $post_id
   * @return void
   */
  public function save_post(int $post_id): void {
    
    $post = get_post($post_id);
    if( in_array($post->post_type, ['revision']) ) return;
    if( !in_array($post->post_status, ['pending']) ) return;
    if( !is_post_type_viewable($post->post_type) ) return;
    if( wp_doing_ajax() ) return;

    // Only send notifications each 5 minutes
    $transient_name = "block_pending_review_notification_for_{$post_id}";
    delete_transient($transient_name);
    if( get_transient($transient_name) ) return;
    set_transient($transient_name, true, MINUTE_IN_SECONDS * 5);

    $mail_success = $this->send_pending_review_notifiation($post_id);
  }

  /**
   * Filter the content type for wp_mail
   *
   * @param string $content_type
   * @return string
   */
  public function wp_mail_content_type(string $content_type): string {
    return "text/html";
  }

  /**
   * Send a notification email each time a post is submitted for review
   *
   * @param integer $post_id
   * @return boolean
   */
  private function send_pending_review_notifiation(int $post_id): bool {
    $edit_link = get_edit_post_link($post_id);

    add_filter('wp_mail_content_type', [$this, 'wp_mail_content_type']);
    
    $emails = $this->get_subscriber_emails();
    if( empty($emails) ) return false;

    $user = wp_get_current_user();
    $post_title = get_the_title($post_id);
    
    $result = wp_mail(
      implode(',', $emails), 
      '[hdm] New pending review', 
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
  private function get_subscriber_emails(): array {
    $subscribers = [];
    $users = get_users([
      'meta_key' => 'subscribe_to_pending_reviews',
      'meta_value' => '1',
      'capability' => 'edit_others_posts'
    ]);
    foreach( $users as $user ) {
      $subscribers[] = $user->data->user_email;
    }
    return $subscribers;
  }

}