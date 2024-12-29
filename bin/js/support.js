// @ts-check

import * as fs from "fs";
import { fileURLToPath } from "url";
import path from "path";
import { execSync } from "child_process";
import { exit } from "process";

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
 * @return {{version: string, name: string}}
 */
export function getPackageInfos() {
  // Read the version and name from package.json
  const packageJsonPath = path.join(process.cwd(), "./package.json");
  const { version, name } = JSON.parse(
    fs.readFileSync(packageJsonPath, "utf8"),
  );
  return { version, name };
}

/**
 * Run a command, stop execution on errors
 * @param {string} command
 */
export function run(command) {
  try {
    execSync(command, { stdio: "inherit" });
  } catch (error) {
    console.error(`❌ Command failed: ${command}`);
    exit(1);
  }
}
