#!/usr/bin/env node

import {
  blue,
  error,
  getInfosFromComposerJSON,
  readFile,
  red,
  runAsScript,
  success,
} from "./lib.js";

/**
 * Extract the first version number from a composer constraint
 * e.g. ">=8.4" => "8.4"
 * @param {string|undefined} constraint
 * @return {string|undefined}
 */
const extractVersion = (constraint) => constraint?.match(/\d+(\.\d+)*/)?.[0];

/**
 * Extract a header from a plugin file or a readme.txt
 * e.g. "Requires PHP: 8.4" => "8.4"
 * @param {string|undefined} contents
 * @param {string} header
 * @return {string|undefined}
 */
const extractHeader = (contents, header) =>
  contents?.match(new RegExp(`^[ \\t/*#@]*${header}:(.*)$`, "mi"))?.[1].trim();

/**
 * Make sure the required PHP version is declared consistently everywhere.
 * Each of these is read by a different consumer:
 * - the main plugin file: WordPress, when activating or installing an upload
 * - readme.txt: plugin-update-checker, when reporting available updates
 * - composer.json `require`: composer, for both composer installs and Strauss
 * - composer.json `config.platform`: composer, when resolving. Without it, resolution
 *   happens against whatever PHP is installed locally, so a dependency needing a newer
 *   patch version than we support would be picked without complaint.
 */
export function checkPHPVersion() {
  const { packageName, dependencies, platform } = getInfosFromComposerJSON();

  /** @type {Record<string, string|undefined>} */
  const versions = {
    [`${packageName}.php`]: extractHeader(
      readFile(`${packageName}.php`),
      "Requires PHP",
    ),
    "readme.txt": extractHeader(readFile("readme.txt"), "Requires PHP"),
    "composer.json (require)": extractVersion(dependencies.php),
    "composer.json (config.platform)": extractVersion(platform.php),
  };

  const declarations = Object.entries(versions)
    .map(([file, version]) => `  - ${blue(file)}: ${version ?? red("missing")}`)
    .join("\n");

  if (Object.values(versions).some((version) => !version)) {
    error(`The required PHP version is not declared everywhere:`, `\n${declarations}`); // prettier-ignore
  }

  if (new Set(Object.values(versions)).size > 1) {
    error(`The required PHP version is declared inconsistently:`, `\n${declarations}`); // prettier-ignore
  }

  success(`Required PHP version is consistently declared as ${Object.values(versions)[0]}`); // prettier-ignore
}

runAsScript(import.meta.url, checkPHPVersion);
