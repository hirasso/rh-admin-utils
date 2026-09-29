/**
 * This runs anytime before committing files
 */
export default {
  "**/*.{js,css,scss}": [
    "prettier --write",
    () => "pnpm run build", // ← ignore files
    () => "git add ./assets", // ← ignore files
  ],
  // Our own sources only: lib/ and vendor-prefixed/ are third-party
  "{*.php,{src,tests,tools}/**/*.php}": [
    "vendor/bin/pint",
    () => "composer analyse", // ← ignore files (otherwise pest files would be analysed, too)
    () => "tools/make-pot.sh", // ← ignore files
    () => "git add ./languages", // ← ignore files
  ],
  "*": () => "pnpm run validate:php",
};
