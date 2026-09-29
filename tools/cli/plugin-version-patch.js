#!/usr/bin/env node

import { readFileSync, writeFileSync } from "node:fs";
import { basename, extname } from "node:path";
import { cwd, exit } from "node:process";
import fg from "fast-glob";
import path from "node:path";

import {
  error,
  getInfosFromComposerJSON,
  info,
  line,
  runAsScript,
  success,
} from "./lib.js";

/**
 * Patch the version in the main plugin php file, based on the
 * current version in the package.json. That one is the source of
 * truth, as releases are handled by @changesets/action.
 */
export async function patchPluginVersion() {
  const { version } = JSON.parse(readFileSync(path.join(cwd(), "package.json"), "utf8")); // prettier-ignore
  const { packageName } = getInfosFromComposerJSON();

  const nameRegexp = /\*\s*Plugin Name:\s*/;
  const versionRegexp = /\*\s*Version:\s*(\d+\.\d+\.\d+)/;

  const fileName = (await fg("*.php")).find((file) => {
    const contents = readFileSync(file, "utf-8");
    return (
      basename(file, extname(file)) === packageName &&
      nameRegexp.test(contents) &&
      versionRegexp.test(contents)
    );
  });

  if (!fileName) {
    return error(`Main plugin file not found for package ${packageName}`);
  }

  const contents = readFileSync(fileName, "utf8");
  const currentVersion = contents.match(versionRegexp)?.[1];

  line();
  info(`Patching version in ${fileName}...`);

  if (!currentVersion) {
    return error(`No version found in file: ${fileName}`);
  }

  if (currentVersion === version) {
    success(`Version already patched in ${fileName}: ${currentVersion}`);
    exit(0);
  }

  writeFileSync(
    fileName,
    contents.replace(versionRegexp, `* Version: ${version}`),
    "utf8",
  );

  success(`Patched version to ${version} in ${fileName}`);
  line();
}

runAsScript(import.meta.url, patchPluginVersion);
