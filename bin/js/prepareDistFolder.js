#!/usr/bin/env node

/**
 * Prepare the dist folder
 * - clones the dist repo into dist/
 * - checks out the empty root commit in dist/
 * - copies all files from scoped/ into dist/
 */

import { cpSync, existsSync, rmSync } from "fs";
import { basename, resolve } from "path";
import { cwd, env } from "process";
import {
  run,
  info,
  success,
  throwError,
  getInfosFromComposerJSON,
  dd,
  validateCWD,
  line,
} from "./support.js";

const { owner, packageName } = getInfosFromComposerJSON();
if (!owner || !packageName) {
  throwError(`Could not read owner and/or packageName`, { owner, packageName });
}

// Ensure the script is run from the project root
if (!validateCWD()) {
  throwError(`${basename(__filename)} must be executed from the package root`);
}

// Check if the `scoped` folder exists
if (!existsSync("scoped")) {
  throwError("The 'scoped' folder does not exist");
}

// Initialize the dist folder if not in GitHub Actions
if (env.GITHUB_ACTIONS !== "true") {
  info(`Cloning the dist repo into dist/...`);
  rmSync("dist", { recursive: true, force: true });
  run(
    `git clone -b empty git@github.com:${owner}/${packageName}-dist.git dist/`,
  );
}

info(`Checking out the empty tagged root commit..`);
run("git -C dist checkout --detach empty");

line();

info(`Copying files from scoped/ to dist/...`);
cpSync("scoped", "dist", { recursive: true, force: true });

success(`Dist folder preparation complete!`);
