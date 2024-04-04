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
    document.addEventListener("focusin", this.onFocusIn, { capture: true });
    document.addEventListener("keydown", this.onKeyDown, { capture: true });
    document.addEventListener("click", this.onClick, { capture: true });

  }

  disconnectedCallback() {
    document.removeEventListener("focusin", this.onFocusIn);
    document.removeEventListener("keydown", this.onKeyDown);
    document.removeEventListener("click", this.onClick);
  }

  onKeyDown = (e) => {
    switch (e.code) {
      case "Space":
        this.handleSpaceDown(e);
        break;
      case "Escape":
        this.handleEscapeDown(e);
        break;
    }
  };

  onFocusIn = (e) => {
    if (!this.isVisible()) return;

    e.stopPropagation();

    if (e.target.matches('rhau-environment-link')) return;

    if (e.target.matches(":first-child")) {
      return this.focusLastLink();
    }

    this.focusFirstLink();

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

  show() {
    if (this.isVisible()) return;
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
    if (!this.isVisible()) return;
    e.preventDefault();
    e.stopPropagation();
    this.hide();
  }

  hide() {
    this.classList.remove("is-visible");
  }

  openEnvironmentLink(el) {
    if (!this.isVisible()) return;

    const localUrl = new URL(window.location.href);
    const localPath = localUrl.pathname + localUrl.search + localUrl.hash;
    const remoteUrl = new URL(el.getAttribute("data-remote-root"));

    window.open(remoteUrl.origin + localPath);

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
