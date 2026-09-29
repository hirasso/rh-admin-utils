#!/usr/bin/env node

import { cpSync, mkdirSync, rmSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { cwd } from "node:process";

import {
  blue,
  getBuildFolder,
  getInfosFromComposerJSON,
  headline,
  info,
  line,
  run,
  runAsScript,
  success,
} from "./lib.js";
import { checkPHPVersion } from "./php-version-check.js";
import { verifyPrefixedDependencies } from "./verify-prefixed.js";

/**
 * Create the release asset.
 * Composer serves the tag's git archive; this zip is that same archive plus built assets.
 */
export async function createRelease() {
  headline(`Creating Release Files...`);

  /** Bail early if the required PHP version got out of sync */
  checkPHPVersion();

  /** Bail early if the committed prefixed dependencies are stale */
  await verifyPrefixedDependencies();

  const { packageName } = getInfosFromComposerJSON();
  const buildFolder = getBuildFolder();

  line();
  info(`Creating a release in ${blue(buildFolder)}...`);
  line();

  /** --worktree-attributes so an uncommitted .gitattributes still applies */
  info(`Exporting tracked files to ${buildFolder}...`);
  rmSync(dirname(buildFolder), { recursive: true, force: true });
  mkdirSync(buildFolder, { recursive: true });
  run(`git archive --worktree-attributes --format=tar HEAD | tar -x -C ${buildFolder}`); // prettier-ignore

  /** `pnpm build` runs before this in CI, so prefer the working tree over HEAD */
  info(`Overlaying freshly built assets into ${buildFolder}...`);
  cpSync("./assets", `${buildFolder}/assets`, { recursive: true, force: true });

  line();

  /** `zip` appends, so a leftover archive would keep stale files */
  info(`Creating a zip file from ${buildFolder}...`);
  rmSync(`${packageName}.zip`, { force: true });
  run(`cd ${buildFolder} && zip -rq "${resolve(cwd(), `${packageName}.zip`)}" . && cd - >/dev/null`); // prettier-ignore

  line();
  success(`Created a release folder: ${blue(buildFolder)}`);
  success(`Created a release asset: ${blue(`${packageName}.zip`)}`);
  line();
}

runAsScript(import.meta.url, createRelease);
