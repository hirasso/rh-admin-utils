import { EditorView, basicSetup } from "codemirror";
import { EditorState } from "@codemirror/state";
import { json } from "@codemirror/lang-json";
import { html } from "@codemirror/lang-html";

/**
 * Converts an ACF textarea field to a code field
 */
export default () => {
  return {
    get codeLanguage() {
      return this.$root.getAttribute("data-rhau-code-language");
    },

    get lineWrappingEnabled() {
      return (
        parseInt(
          this.$root.getAttribute("data-rhau-code-line-wrapping"),
          10
        ) === 1
      );
    },

    init() {
      const isClone = this.$root.closest(".acf-clone") !== null;
      if (!isClone) this.renderEditor();
    },

    renderEditor() {
      const textarea = this.$root.querySelector("textarea");
      textarea.style.display = "none";

      const extensions = [
        basicSetup,
        /**
         * Set readonly based on textarea value
         */
        EditorState.readOnly.of(textarea.readOnly),
        /**
         * Listen for updates
         * @see https://discuss.codemirror.net/t/codemirror-6-proper-way-to-listen-for-changes/2395/11
         */
        EditorView.updateListener.of((viewUpdate) => {
          if (!viewUpdate.docChanged) return;
          textarea.value = viewUpdate.state.doc.toString();
        }),
      ];

      /**
       * Allow line wrapping
       * @see https://discuss.codemirror.net/t/word-wrapping/4512
       */
      if (this.lineWrappingEnabled) {
        extensions.push(EditorView.lineWrapping);
      }

      switch (this.codeLanguage) {
        case "json":
          extensions.push(json());
          break;
        case "html":
          extensions.push(html());
          break;
        default:
          // Fall back to json for backwards compatibility
          extensions.push(json());
          break;
      }

      const view = new EditorView({
        doc: textarea.value,
        parent: this.$el,
        extensions,
      });
    },
  };
};
