/**
 * Render a TinyMCE button to add a link to a file to selected text
 */
export default function (editor) {
  const disabledToolTip = "Select some text to enable";
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
   * Add node change event listener to enable/disable the button based on text selection
   */
  editor.on("NodeChange", function (e) {
    const button = editor.controlManager.buttons["rhau_link_to_file"];

    if (!button) {
      return;
    }

    const selection = editor.selection.getContent();

    const hasSelection = !!selection;

    button.disabled(!hasSelection);

    button.settings.tooltip = hasSelection ? enabledToolTip : disabledToolTip;
  });
}
