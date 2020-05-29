
var jQuery = $ = global.jQuery;

import './scss/admin.scss';

export default class RHAU {

  constructor() {
    jQuery(document).ready(() => this.onDocReady());
    this.restoreScrollTop();
  }

  /**
   * Is run on doc ready
   */
  onDocReady() {
    this.restoreScrollTop(true);
    this.enableSubmitDiv();
    $('form#post').submit((e) => this.storeScrollTop());
    this.initAdminBarButtons();
  }

  /**
   * Restores scrollTop
   * @param {boolean} deleteValue 
   */
  restoreScrollTop(deleteValue = false) {
    var scrollTop = sessionStorage.getItem(this.getStorageKey('scrollTop'));
    if( deleteValue ) sessionStorage.removeItem(this.getStorageKey('scrollTop'));
    if( scrollTop ) $(document).scrollTop(parseInt(scrollTop));
  }

  /**
   * Inits Admin Bar Buttons
   */
  initAdminBarButtons() {
    // publish/update button
    let $wpPublish = $('#submitdiv input[id="publish"]:visible');
    let $rhPublish = $('#wp-admin-bar-rh-publish a');
    if( $wpPublish.length ) {
      $rhPublish.text($wpPublish.val()).click(e => {
        e.preventDefault();
        $wpPublish.click();
        $rhPublish.addClass('is-disabled');
      })
    }
    // save draft button
    let $wpSave = $('#submitdiv input[id="save-post"]:visible');
    let $rhSave = $('#wp-admin-bar-rh-save a');
    if( $wpSave.length ) {
      $rhSave.text($wpSave.val()).click(e => {
        e.preventDefault();
        $wpSave.click();
        $rhSave.addClass('is-disabled');
      })
    }
  }

  /**
   * Enables the #submitdiv
   */
  enableSubmitDiv() {
    $('#submitdiv').addClass("rhau-enabled");
  }

  /**
   * Store current scrollTop
   */
  storeScrollTop() {
    sessionStorage.setItem(this.getStorageKey('scrollTop'), $(document).scrollTop());
  }

  /**
   * Get storage key for scrollTop
   */
  getStorageKey(key) {
    var path = window.location.pathname + window.location.search;
    return key + ":" + path.replace(/^\/+/g, '').split('/').join('-');
  }

}

new RHAU();