import addLinkToFileButton from "./tinymce/link-to-file.js";

(function () {
  tinymce.create("tinymce.plugins.rhauTinyMcePlugins", {
    init: function (editor, url) {
      addLinkToFileButton(editor);
    },
    createControl: function (n, cm) {
      return null;
    },
  });

  tinymce.PluginManager.add(
    "rhauTinyMcePlugins",
    tinymce.plugins.rhauTinyMcePlugins,
  );
})();
