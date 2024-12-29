#!/usr/bin/env node

// @ts-check

import * as fs from "fs";
import { fileURLToPath } from "url";
import path, { basename } from "path";
import { execSync } from "child_process";
import { cwd, env, exit } from "process";
import pc from "picocolors";

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

/**
 * Log an info message
 * @param {string} message
 * @param {...any[]} rest
 */
export const info = (message, ...rest) => {
  console.log(`💡${pc.gray(message)}`, ...rest);
};

/**
 * Log a success message
 * @param {string} message
 * @param {...any[]} rest
 */
export const success = (message, ...rest) => {
  console.log(`✅${pc.green(message)}`, ...rest);
};

/**
 * Log an error message and exit
 * @param {string} message
 * @param {...any[]} rest
 */
export const error = (message, ...rest) => {
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
 * @param {...any[]} args
 */
export const debug = (...args) => {
  line();
  console.log('🐛', ...args);
  line();
};

/**
 * Check if currently running on GitHub actions
 */
export const isGitHubActions = () => env.GITHUB_ACTIONS === "true";
