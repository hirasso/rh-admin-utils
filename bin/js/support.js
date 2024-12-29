// @ts-check

import * as fs from "fs";
import { fileURLToPath } from "url";
import path from "path";
import { execSync } from "child_process";
import { exit } from "process";
import pc from "picocolors";

// Get the directory of the current script
const __filename = fileURLToPath(import.meta.url);

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
 * In this project, the version in package.json is the
 * source of truth, as releases are handled by @changesets/action
 * @return {{version: string}}
 */
export function getVersionFromPackageJSON() {
  // Read the version and name from package.json
  const packageJsonPath = path.join(process.cwd(), "./package.json");
  const { version } = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"));
  return { version };
}

/**
 * Get infos from the composer.json
 * @return {{fullName: string, owner: string, packageName: string}}
 */
export function getInfosFromComposerJSON() {
  // Read the version and name from package.json
  const composerJsonPath = path.join(process.cwd(), "./composer.json");
  const { name: fullName } = JSON.parse(
    fs.readFileSync(composerJsonPath, "utf8"),
  );
  const [owner, packageName] = fullName.split("/");
  return { fullName, owner, packageName };
}

/**
 * Run a command, stop execution on errors ({ stdio: "inherit" })
 * @param {string} command
 */
export const run = (command) => execSync(command, { stdio: "inherit" });

/** log an info @param {string} message */
export const info = (message) => console.log(`💡${pc.gray(message)}`);

/** log a success @param {string} message */
export const success = (message) => console.log(`✅${pc.green(message)}`);

/** log an error and exit @param {string} message */
export const error = (message) => {
  console.log(`❌${pc.red(message)}`);
  exit(1);
};

/** log a line */
export const line = () => console.log("");
