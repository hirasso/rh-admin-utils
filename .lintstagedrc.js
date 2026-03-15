/**
 * This runs anytime before committing files
 */
export default {
  "**/*.{js,css,scss}": [
    "prettier --write",
    () => "pnpm run build", // ← ignore files
    () => "git add ./assets", // ← ignore files
  ],
  "**/*.php": [
    "vendor/bin/phpstan analyze --memory-limit=2G",
    "vendor/bin/pint",
    () => "tools/make-pot.sh", // ← ignore files
  ],
};
