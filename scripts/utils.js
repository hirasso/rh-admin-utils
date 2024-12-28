// @ts-check

import * as fs from "fs";
import { fileURLToPath } from "url";
import path from "path";

// Get the directory of the current script
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Dump and die
 * @param {...any} args
 */
export function dd(...args) {
  console.log(...args);
  process.exit();
}

/**
 * Get the current version from the package.json
 */
export function getVersion() {
  // Read the version and name from package.json
  const packageJsonPath = path.join(__dirname, "../package.json");
  const { version } = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"));
  return version ?? "";
}
