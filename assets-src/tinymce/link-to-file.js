/**
 * Render a TinyMCE button to add a link to a file to selected text
 */
export default function (editor) {
  const disabledToolTip = "Select some plain text to enable";
  const enabledToolTip = "Add link to file";

  editor.addButton("rhau_link_to_file", {
    title: "Add link to file",
    tooltip: disabledToolTip,
    icon: "rhau-link-to-file dashicons dashicons-paperclip",
    disabled: true,
    onclick: selectFile,

    onPostRender: function () {
      const button = this.getEl().querySelector("button");
      /** Optionally, we could render a custom icon from feather-icons here */
    },
  });

  function selectFile() {
    const selection = editor.selection.getContent();

    if (!selection) {
      alert("Please select the text you want to link.");
      return;
    }

    const dialog = wp.media({
      title: "Select File",
      library: { type: ["application/pdf", "application/zip"] },
      multiple: false,
      button: { text: "Select" },
    });

    function onSelect() {
      const attachment = dialog.state().get("selection").first().toJSON();

      editor.execCommand(
        "mceInsertContent",
        false,
        `<a href="${attachment.url}" target="_blank" download>${selection}</a>`
      );
    }

    dialog.on("select", onSelect).open();
  }

  /**
   * Toggle the button state, based on the current selection
   */
  function toggleButtonState() {
    const button = editor.controlManager.buttons["rhau_link_to_file"];

    if (!button) {
      return;
    }

    const selection = editor.selection.getContent();

    const hasSelection = !!selection;

    const isOnlyPlainTextSelected = !/<\/?[a-z][\s\S]*>/i.test(selection);

    const isDisabled = !hasSelection || !isOnlyPlainTextSelected;

    button.disabled(isDisabled);

    button.settings.tooltip = isDisabled ? disabledToolTip : enabledToolTip;
  }

  editor.on("NodeChange", toggleButtonState);
  editor.on("KeyUp", toggleButtonState);
  editor.on("KeyDown", toggleButtonState);
  editor.on("MouseUp", toggleButtonState);
  editor.on("MouseDown", toggleButtonState);
}
