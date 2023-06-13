/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets-src/scss/modules/rhau-environment-links.scss":
/*!*************************************************************!*\
  !*** ./assets-src/scss/modules/rhau-environment-links.scss ***!
  \*************************************************************/
/***/ (function() {

// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 	
/******/ 		        // webpack-livereload-plugin
/******/ 		        (function() {
/******/ 		          if (typeof window === "undefined") { return };
/******/ 		          var id = "webpack-livereload-plugin-script-72ab59558f0900f0";
/******/ 		          if (document.getElementById(id)) { return; }
/******/ 		          var el = document.createElement("script");
/******/ 		          el.id = id;
/******/ 		          el.async = true;
/******/ 		          el.src = "//" + location.hostname + ":35729/livereload.js";
/******/ 		          document.getElementsByTagName("head")[0].appendChild(el);
/******/ 		          console.log("[Live Reload] enabled");
/******/ 		        }());
/******/ 		        // Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!**********************************************!*\
  !*** ./assets-src/rhau-environment-links.js ***!
  \**********************************************/
/* harmony import */ var _scss_modules_rhau_environment_links_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./scss/modules/rhau-environment-links.scss */ "./assets-src/scss/modules/rhau-environment-links.scss");
const $ = window.jQuery;

class EnvironmentLinks extends HTMLElement {
  constructor() {
    super();
    this.onKeyDown = (e) => {
      switch (e.code) {
        case "Space":
          this.handleSpaceDown(e);
          break;
        case "Escape":
          this.handleEscapeDown(e);
          break;
      }
    };
    this.onFocusIn = (e) => {
      if (!this.isVisible())
        return;
      e.stopPropagation();
      if (e.target.matches("rhau-environment-link"))
        return;
      if (e.target.matches(":first-child")) {
        return this.focusLastLink();
      }
      this.focusFirstLink();
    };
    this.onClick = (e) => {
      if (e.target.matches("rhau-environment-link")) {
        return this.openEnvironmentLink(e.target);
      }
      if (e.target.closest("rhau-environment-links"))
        return;
      if (!this.isVisible())
        return;
      e.stopPropagation();
      this.hide();
    };
    this.handleSpaceDown = (e) => {
      if (this.isInputElement(e.target) || this.isSpecialKeyDown(e))
        return;
      e.preventDefault();
      e.stopPropagation();
      if (e.target.matches("rhau-environment-link")) {
        return this.openEnvironmentLink(e.target);
      }
      this.show();
    };
    this.attachShadow({
      mode: "open",
      delegatesFocus: true
    });
    this.shadowRoot.innerHTML = /*html*/
    `<slot>`;
  }
  connectedCallback() {
    document.addEventListener("focusin", this.onFocusIn, { capture: true });
    document.addEventListener("keydown", this.onKeyDown, { capture: true });
    document.addEventListener("click", this.onClick, { capture: true });
  }
  disconnectedCallback() {
    document.removeEventListener("focusin", this.onFocusIn);
    document.removeEventListener("keydown", this.onKeyDown);
    document.removeEventListener("click", this.onClick);
  }
  isVisible() {
    return this.classList.contains("is-visible");
  }
  isInputElement(el) {
    return el == null ? void 0 : el.matches(`button, input, textarea, [contenteditable="true"]`);
  }
  isSpecialKeyDown(e) {
    return e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
  }
  show() {
    if (this.isVisible())
      return;
    this.classList.add("is-visible");
    this.focusFirstLink();
  }
  focusFirstLink() {
    this.querySelector("rhau-environment-link:first-of-type").focus();
  }
  focusLastLink() {
    this.querySelector("rhau-environment-link:last-of-type").focus();
  }
  handleEscapeDown(e) {
    if (!this.isVisible())
      return;
    e.preventDefault();
    e.stopPropagation();
    this.hide();
  }
  hide() {
    this.classList.remove("is-visible");
  }
  openEnvironmentLink(el) {
    if (!this.isVisible())
      return;
    const localUrl = window.location.href;
    const remoteRoot = el.getAttribute("data-remote-root");
    const regexp = new RegExp(this.getAttribute("data-dev-root"), "gi");
    const remoteUrl = localUrl.replace(regexp, remoteRoot);
    window.open(remoteUrl);
    this.hide();
  }
  trapFocus(e) {
    if (!e.target.matches("rhau-environment-link:last-of-type")) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
}
customElements.define("rhau-environment-links", EnvironmentLinks);

}();
/******/ })()
;
//# sourceMappingURL=rhau-environment-links.js.map