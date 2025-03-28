/**
 * Grab global variables
 */
const $ = window.jQuery;
const acf = window.acf;

import "./scss/rhau.scss";

import Alpine from "alpinejs";
import mask from "@alpinejs/mask";
Alpine.plugin(mask);

import ACFPasswordUtilities from "./components/alpine/ACFPasswordUtilities/ACFPasswordUtilities.js";
import ACFCodeField from "./components/alpine/ACFCodeField/ACFCodeField.js";
import ACFRelationshipField from "./components/alpine/ACFRelationshipField/ACFRelationshipField.js";
import ACFTextField from "./components/alpine/ACFTextField/ACFTextField.js";

export default class RHAU {
  constructor() {
    $(document).ready(() => this.onDocReady());

    $("form#post").one("submit", (e) => this.beforeSubmitPostForm(e));

    this.injectColorThemeVars();

    this.initAlpine();

    // Handle ACF fields
    this.handleACFWysiwygField();

    // acfRelationshipAddOrderControl();
  }

  initAlpine() {
    Alpine.prefix("rhau-x-");

    Alpine.data("ACFPasswordUtilities", (options) =>
      ACFPasswordUtilities(options),
    );
    Alpine.data("ACFCodeField", (options) => ACFCodeField(options));
    Alpine.data("ACFRelationshipField", (options) =>
      ACFRelationshipField(options),
    );
    Alpine.data("ACFTextField", () => ACFTextField());

    Alpine.start();
  }

  injectColorThemeVars() {
    const button = document.createElement("a");
    button.classList.add("button", "button-primary");
    document.body.prepend(button);
    const buttonStyle = window.getComputedStyle(button);
    this.setCssVar("--rhau-button-primary-color", buttonStyle.color);
    this.setCssVar(
      "--rhau-button-primary-background",
      buttonStyle.backgroundColor,
    );
    button.remove();
  }

  setCssVar(name, value) {
    document.documentElement.style.setProperty(name, value);
  }

  /**
   * This runs on doc ready
   */
  onDocReady() {
    this.enableSubmitDiv();
    this.initAdminBarButtons();
    this.reopenSavedAcfFieldObjects();
    this.restoreScrollTop();
    this.removeFromStore("scrollTop");
    requestAnimationFrame(() => this.initQtranslateSwitcher());
    // Try again, in case qtranslate took a while to initialize
    setTimeout(() => this.initQtranslateSwitcher(), 1000);
    setTimeout(() => this.initQtranslateSwitcher(), 2000);
  }

  /**
   * Is being fired before saving/publishing/updating a post
   */
  beforeSubmitPostForm(e) {
    // e.preventDefault();
    this.saveOpenAcfFieldObjects();
    this.addToStore("scrollTop", $(document).scrollTop());
  }

  /**
   * Restores scrollTop
   */
  restoreScrollTop() {
    var scrollTop = this.getFromStore("scrollTop");
    if (scrollTop) $(document).scrollTop(parseInt(scrollTop));
  }

  /**
   * Inits Admin Bar Buttons
   */
  initAdminBarButtons() {
    // publish/update button
    const $wpPublish = $('#submitdiv input[id="publish"]:visible');
    const $rhPublish = $("#wp-admin-bar-rh-publish a");
    if ($rhPublish.length && $wpPublish.length) {
      $rhPublish.text($wpPublish.val()).click((e) => {
        e.preventDefault();
        $wpPublish.click();
        $rhPublish.addClass("is-disabled");
      });
    } else if ($rhPublish.length) {
      $rhPublish.parent().remove();
    }
    // save draft button
    const $wpSave = $('#submitdiv input[id="save-post"]:visible');
    const $rhSave = $("#wp-admin-bar-rh-save a");
    if ($rhSave.length && $wpSave.length) {
      $rhSave.text($wpSave.val()).click((e) => {
        e.preventDefault();
        $wpSave.click();
        $rhSave.addClass("is-disabled");
      });
    } else if ($rhSave.length) {
      $rhSave.parent().remove();
    }
    if (typeof acf !== "undefined" && typeof acf.addFilter !== "undefined") {
      acf.addFilter("validation_complete", (json, $form) => {
        // check errors
        if (json.errors) {
          if ($rhPublish.length) $rhPublish.removeClass("is-disabled");
          if ($rhSave.length) $rhSave.removeClass("is-disabled");
        }

        // return
        return json;
      });
    }
  }

  /**
   * Enables the #submitdiv
   */
  enableSubmitDiv() {
    $("#submitdiv").addClass("rhau-enabled");
  }

  /**
   * Stores something in session storage
   *
   * @param {string} key
   * @param {mixed} value
   */
  addToStore(key, value) {
    sessionStorage.setItem(this.getStorageKey(key), JSON.stringify(value));
  }

  /**
   * Removes something from session storage
   * @param {string} key
   */
  removeFromStore(key) {
    sessionStorage.removeItem(this.getStorageKey(key));
  }

  /**
   * Gets something from store
   * @param {string} key
   */
  getFromStore(key) {
    let value = sessionStorage.getItem(this.getStorageKey(key));
    return value ? JSON.parse(value) : value;
  }

  /**
   * Stores open Field objects
   */
  saveOpenAcfFieldObjects() {
    if (typeof acf === "undefined") return;
    if (typeof acf.getFieldObjects !== "function") return;
    let openFieldObjects = [];
    try {
      for (const fieldObject of acf.getFieldObjects()) {
        if (!fieldObject.isOpen()) continue;
        // disable close function
        fieldObject.close = () => {};
        openFieldObjects.push(fieldObject.getKey());
      }
    } catch (e) {
      console.warn(e);
    }
    this.addToStore("open-acf-field-objects", openFieldObjects);
  }

  /**
   *
   * @param {mixed} obj
   * @param  {...any} args
   */
  getNested(obj, ...args) {
    return args.reduce((obj, level) => obj && obj[level], obj);
  }

  /**
   * Restores open field objects
   */
  reopenSavedAcfFieldObjects() {
    if (typeof acf === "undefined") return;
    if (typeof acf.getFieldObjects !== "function") return;
    let openObjects = this.getFromStore("open-acf-field-objects");
    if (!openObjects) return;
    try {
      for (const key of openObjects) {
        const fieldObject = acf.getFieldObject(key);
        const $settings = fieldObject.$el.children(".settings");
        // copied code from acf-field-group.js
        // open
        fieldObject.$el.addClass("open");
        $settings.css({ display: "block" });
        acf.doAction("open_field_object", fieldObject);
        fieldObject.trigger("openFieldObject");
        // action (show)
        acf.doAction("show", $settings);
      }
    } catch (e) {
      console.warn(e);
    }
    // removes the property from store
    this.removeFromStore("open-acf-field-objects");
  }

  /**
   * Get storage key for scrollTop
   */
  getStorageKey(key) {
    var path = window.location.pathname;
    return key + ":" + path.replace(/^\/+/g, "").split("/").join("-");
  }

  /**
   * Copy the qtranslate language switcher to the admin bar.
   * This function is being called twice, to make sure it works everywehre.
   */
  initQtranslateSwitcher() {
    // Bail early if it already ran successfully
    const alreadyRendered = $("#wp-admin-bar-rhau-lsbs").length > 0;
    if (alreadyRendered) return;

    const $switcher = $(".qtranxs-lang-switch-wrap:first");
    // bail early if no switcher could be found
    if ($switcher.length === 0) return;
    // create the wrapper
    const $wrap = $('<li id="wp-admin-bar-rhau-lsbs" class="rhau-lsbs" />');
    $wrap.appendTo($("#wp-admin-bar-root-default"));
    // append the switcher to the wrap
    $switcher.appendTo($wrap);
  }

  /**
   * Attaches an intersection observer to delayed WYSIWYG fields
   * to enable them automatically if scrolled into view
   *
   * Uses the `ready_field` and field.addAction('load') actions
   * @see https://www.advancedcustomfields.com/resources/javascript-api/#actions-ready_field
   * @see https://www.advancedcustomfields.com/resources/javascript-api/#actions-load_field
   */
  handleACFWysiwygField = () => {
    /**
     * Is the field delayed?
     */
    const isDelayed = (field) => {
      return field.$control().hasClass("delay");
    };

    /**
     * This function will be injected into the ACF field object.
     * So `this` will be the field
     */
    function observe() {
      if (!isDelayed(this)) {
        return;
      }
      const field = this;

      const $wrap = field.$control();
      const observer = new IntersectionObserver(
        (entries) => {
          if (entries[0].isIntersecting) {
            $wrap.trigger("mousedown");

            observer.disconnect();
          }
        },
        {
          rootMargin: "300px 0px",
        },
      );

      observer.observe($wrap[0]);
    }

    /**
     * As soon as the field is ready, prepare it
     * for observation
     */
    function onReady(field) {
      if (!isDelayed(field)) {
        return;
      }

      field
        .$control()
        .find(".acf-editor-toolbar")
        .text("Initializing Editor ...");

      field.observe = observe.bind(field);

      field.addAction("load", field.observe);
    }
    acf?.addAction("ready_field/type=wysiwyg", onReady);
  };
}

new RHAU();
