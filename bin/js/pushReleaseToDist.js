#!/usr/bin/env node

/**
 * Push a new release to the dist repo
 */

import { basename } from "path";
import { chdir, cwd } from "process";
import { fileURLToPath } from "url";
import {
  run,
  error,
  dd,
  getInfosFromPackageJSON,
  getInfosFromComposerJSON,
  info,
  line,
  debug,
  validateCWD,
  isGitHubActions,
} from "./support.js";
import { existsSync } from "fs";

const rootDir = cwd();
const __filename = fileURLToPath(import.meta.url);

const onGitHub = isGitHubActions();

debug({ onGitHub });

if (!validateCWD()) {
  error(`${basename(__filename)} must be executed from the package root`);
}

/**
 * Check if the `dist` folder exists
 */
if (!existsSync("dist") || !existsSync("dist/.git")) {
  error(
    "The 'dist' folder does not exist. Please run 'bin/prepareDistFolder' first"
  );
}

/** Ensure the script is running in a GitHub Action */
if (!onGitHub) {
  error(`${basename(__filename)} can only run on GitHub`);
}

/** Get the package version and name */

const packageInfos = getInfosFromPackageJSON();
const { packageName } = getInfosFromComposerJSON();

if (!packageVersion) {
  error("Empty package version");
}

const packageVersion = `v${packageInfos.version}`;

info(`Committing and pushing new release: 'v${packageVersion}'...`);
line();

/** Navigate to the dist folder and perform Git operations */
try {
  chdir("dist/");
  run(`git add .`);
  run(`git commit -m "Release: ${packageName}@${packageVersion}"`);
  run(`git tag "${packageVersion}"`);
  run(`git push origin "${packageVersion}"`);
  success(`Released '${packageVersion}' to the dist repo.`);
  chdir(rootDir);
} catch (err) {
  error("An error occurred while releasing the package.", err);
}

/** Change back to the root dir */
chdir(rootDir);