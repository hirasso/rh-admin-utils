import "./environment-links.scss";

class EnvironmentLinksDialog extends HTMLDialogElement {
  shadowRoot: ShadowRoot;

  constructor() {
    super();

    // this.attachShadow({
    //   mode: "open",
    //   delegatesFocus: true,
    // });
  }

  connectedCallback() {
    document.addEventListener("keydown", this.onKeyDown, { capture: true });
    document.addEventListener("click", this.onClick, { capture: true });
  }

  disconnectedCallback() {
    document.removeEventListener("keydown", this.onKeyDown);
    document.removeEventListener("click", this.onClick);
  }

  onKeyDown = (e: KeyboardEvent) => {
    switch (e.code) {
      case "Space":
        this.handleSpaceDown(e);
        break;
      case "Escape":
        this.handleEscapeDown(e);
        break;
    }
  };

  onClick = (e: MouseEvent) => {
    const target = e.target as HTMLElement;

    if (target.matches("rhau-environment-link")) {
      return this.openEnvironmentLink(e.target);
    }

    if (target.closest("rhau-environment-links")) return;
    if (!this.open) return;

    e.stopPropagation();
    this.close();
  };

  isInputElement(el: HTMLElement) {
    if (el?.closest("rhau-environment-link")) {
      return false;
    }

    // Check if the element has a valid tabindex
    const tabindex = el?.getAttribute("tabindex");
    if (tabindex !== null) {
      return !isNaN(parseInt(tabindex)) && parseInt(tabindex) >= 0;
    }

    return el?.matches(
      `button, input, textarea, select, a, [contenteditable="true"]`,
    );
  }

  isSpecialKeyDown(e: KeyboardEvent) {
    return e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
  }

  handleSpaceDown = (e: KeyboardEvent) => {
    const target = e.target as HTMLElement;

    if (this.isInputElement(target) || this.isSpecialKeyDown(e)) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    if (target.matches("rhau-environment-link")) {
      this.openEnvironmentLink(e.target);
      return;
    }

    this.showModal();
    this.focusFirstLink();
  };

  focusFirstLink() {
    this.querySelector<HTMLElement>(
      "rhau-environment-link:first-of-type",
    )?.focus();
  }

  focusLastLink() {
    this.querySelector<HTMLElement>(
      "rhau-environment-link:last-of-type",
    )?.focus();
  }

  handleEscapeDown(e) {
    if (!this.open) return;

    e.preventDefault();
    e.stopPropagation();

    this.close();
  }

  openEnvironmentLink(el) {
    if (!this.open) return;

    const localUrl = new URL(window.location.href);
    const localPath = localUrl.pathname + localUrl.search + localUrl.hash;
    const remoteUrl = new URL(el.getAttribute("data-remote-host"));

    window.open(remoteUrl.origin + localPath);

    this.close();
  }
}

customElements.define("rhau-environment-links", EnvironmentLinksDialog, {
  extends: "dialog",
});
