const $ = window.jQuery;

import "./scss/modules/rhau-environment-links.scss";

class EnvironmentLinks extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({
      mode: "open",
      delegatesFocus: true,
    });
    this.shadowRoot.innerHTML = /*html*/ `<slot>`;
  }

  connectedCallback() {
    window.addEventListener("keydown", this.onKeyDown);
    window.addEventListener("click", this.onClick, { capture: true });
  }

  disconnectedCallback() {
    window.removeEventListener("keydown", this.onKeyDown);
    window.removeEventListener("click", this.onClick);
  }

  onKeyDown = (e) => {
    switch (e.code) {
      case "Space":
        this.handleSpaceDown(e);
        break;
      case "Escape":
        e.preventDefault();
        e.stopPropagation();
        this.hide();
        break;
      case "Tab":
        this.handleTabDown(e);
        break;
    }
  };

  onClick = (e) => {
    if (e.target.matches("rhau-environment-link")) {
      return this.openEnvironmentLink(e.target);
    }

    if (e.target.closest("rhau-environment-links")) return;
    if (!this.isVisible()) return;

    e.stopPropagation();
    this.hide();
  };

  isVisible() {
    return this.classList.contains("is-visible");
  }

  isInputElement(el) {
    return el?.matches(`button, input, textarea, [contenteditable="true"]`);
  }

  isSpecialKeyDown(e) {
    return e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
  }

  handleSpaceDown = (e) => {
    if (this.isInputElement(e.target) || this.isSpecialKeyDown(e)) return;

    e.preventDefault();
    e.stopPropagation();

    if (e.target.matches("rhau-environment-link")) {
      return this.openEnvironmentLink(e.target);
    }
    this.show();
  };

  handleTabDown = (e) => {
    if (this.isInputElement(e.target)) return;

    if (e.target.matches("rhau-environment-link:last-child")) {
      e.preventDefault();
      e.stopPropagation();
      this.focusFirstLink();
    }
  };

  show() {
    if (this.isVisible()) return;
    this.classList.add("is-visible");
    this.focusFirstLink();
  }

  focusFirstLink() {
    this.querySelector("rhau-environment-link:first-child").focus();
  }

  hide() {
    if (!this.isVisible()) return;
    this.classList.remove("is-visible");
  }

  openEnvironmentLink(el) {
    if (!this.isVisible()) return;

    const localUrl = window.location.href;
    const remoteRoot = el.getAttribute("data-remote-root");

    const regexp = new RegExp(this.getAttribute("data-dev-root"), "gi");
    const remoteUrl = localUrl.replace(regexp, remoteRoot);

    window.open(remoteUrl);

    this.hide();
  }

  trapFocus(e) {
    if (!e.target.matches("rhau-environment-link:last-child")) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
}

customElements.define("rhau-environment-links", EnvironmentLinks);
