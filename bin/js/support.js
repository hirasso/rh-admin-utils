#!/usr/bin/env node

// @ts-check

import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import path, { basename } from "node:path";
import { execSync } from "node:child_process";
import { cwd, env, exit } from "node:process";
import pc from "picocolors";
import glob from "fast-glob";

// Get the equivalent of __filename
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
 * Validate that the script is being run from the root dir
 * This is being achieved by comparing the package name to
 */
export function validateCWD() {
  const { packageName } = getInfosFromComposerJSON();
  const dirName = basename(cwd());
  return packageName === dirName;
}

/**
 * Get the current version from the package.json
 * In this project, the version in package.json is the
 * source of truth, as releases are handled by @changesets/action
 * @return {{version: string}}
 */
export function getInfosFromPackageJSON() {
  // Read the version and name from package.json
  const packageJsonPath = path.join(process.cwd(), "./package.json");
  const { version } = JSON.parse(readFileSync(packageJsonPath, "utf8"));
  return { version };
}

/**
 * Get infos from the composer.json
 * @return {{fullName: string, owner: string, packageName: string}}
 */
export function getInfosFromComposerJSON() {
  // Read the version and name from package.json
  const composerJsonPath = path.join(process.cwd(), "./composer.json");
  const { name: fullName } = JSON.parse(readFileSync(composerJsonPath, "utf8"));
  const [owner, packageName] = fullName.split("/");
  return { fullName, owner, packageName };
}

/**
 * Run a command, stop execution on errors ({ stdio: "inherit" })
 * @param {string} command
 */
export const run = (command) => execSync(command, { stdio: "inherit" });

/**
 * Log an info message
 * @param {string} message
 * @param {...any} rest
 */
export const info = (message, ...rest) => {
  console.log(`💡${pc.gray(message)}`, ...rest);
};

/**
 * Log a success message
 * @param {string} message
 * @param {...any} rest
 */
export const success = (message, ...rest) => {
  console.log(`✅${pc.green(message)}`, ...rest);
};

/**
 * Log an error message and exit
 * @param {string} message
 * @param {...any} rest
 */
export const throwError = (message, ...rest) => {
  line();
  console.log(` ❌ ${pc.bgRed(pc.bold(`${message}`))}`, ...rest);
  exit(1);
};

/**
 * Log a line
 */
export const line = () => console.log("");

/**
 * Debug something to the console
 * @param {...any} args
 */
export const debug = (...args) => {
  line();
  console.log("🐛", ...args);
  line();
};

/**
 * Check if currently running on GitHub actions
 */
export const isGitHubActions = () => env.GITHUB_ACTIONS === "true";

/**
 * Compare two directories
 * @param {string} dir1
 * @param {string} dir2
 * @param {string[]} ignore
 */
export const validateDirectories = async (dir1, dir2, ignore = [".git"]) => {
  try {
    const pattern = ["*", ...ignore.map((ig) => `!${ig}`)];

    const files1 = await glob(pattern, {
      cwd: dir1,
      onlyFiles: false,
    });
    const files2 = await glob(pattern, {
      cwd: dir2,
      onlyFiles: false,
    });

    const normalized1 = files1.sort();
    const normalized2 = files2.sort();

    // debug(`${dir1}:`, normalized1, `${dir2}:`, normalized2);

    return (
      !!normalized1.length &&
      !!normalized2.length &&
      normalized1.length === normalized2.length &&
      normalized1.every((file, index) => file === normalized2[index])
    );
  } catch (err) {
    throwError("Error comparing directories:", err);
  }
};
