#!/usr/bin/env node

/**
 * Generate release files for usage in the release asset and dist repo
 * - scopes dependency namespaces using php-scoper
 * - creates a folder scoped/ with all required plugin files
 * - creates a zip file from the scoped/ folder, named after the package
 */

import { existsSync, rmSync } from "fs";
import { basename, resolve } from "path";
import { cwd, env } from "process";
import {
  error,
  dd,
  info,
  run,
  line,
  success,
  getInfosFromComposerJSON,
} from "./support.js";

/** Validate that we are at the project root */
const projectRoot = cwd();
if (!existsSync(resolve(projectRoot, ".gitignore"))) {
  error(`${basename(__filename)} must run from the package root`);
}

const { fullName, packageName } = getInfosFromComposerJSON();

line();
info(`Creating a scoped release for ${fullName}...`);
line();

// Install Composer dependencies in GitHub Actions
if (env.GITHUB_ACTIONS === "true") {
  console.log("💡 Installing composer dependencies...");
  run("composer install --no-scripts");
}

/** Ensure php-scoper is available */
const phpScoperPath = "bin/php-scoper";
info("Ensuring php-scoper is available...");
if (!existsSync(phpScoperPath)) {
  run(`curl -sL https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar -o ${phpScoperPath}`); // prettier-ignore
  run(`chmod +x ${phpScoperPath}`);
}

/** Scope namespaces using php-scoper */
info("Scoping namespaces using php-scoper...");
rmSync("scoped", { recursive: true, force: true });
run(`${phpScoperPath} add-prefix --quiet --output-dir=scoped --config=bin/scoper.config.php`); // prettier-ignore
success("Successfully scoped all namespaces!");
line();

/** Dump the autoloader in the scoped directory */
info("Dumping the autoloader in the scoped directory...");
run("composer dump-autoload --working-dir=scoped --classmap-authoritative");

line();

/** Clean up the scoped directory */
info("Cleaning up the scoped directory...");
["scoped/composer.json", "scoped/composer.lock"].forEach((file) => {
  rmSync(resolve(projectRoot, file), { force: true });
});

/** Create a zip file from the scoped directory */
info("Creating a zip file from the scoped directory...");
run(`cd scoped && zip -rq "../${packageName}.zip" . && cd ..`);

line();
success(`Created a scoped release folder: scoped/`);
success(`Created a scoped release asset: ${packageName}.zip`);
line();
