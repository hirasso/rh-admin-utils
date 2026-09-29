import { existsSync, readFileSync } from "node:fs";
import path, { resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { execSync } from "node:child_process";
import { cwd, exit } from "node:process";
import pc from "picocolors";

/** extract colors from common.js module picocolors */
export const { blue, red, bold, gray, green } = pc;

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
  console.log(`💡 ${gray(message)}`, ...rest);
};

/**
 * Log a success message
 * @param {string} message
 * @param {...any} rest
 */
export const success = (message, ...rest) => {
  console.log(`✅ ${green(message)}`, ...rest);
};

/**
 * Log a headline
 * @param {string} message
 */
export const headline = (message) => {
  message = ` ℹ️  ${message} `;
  line();
  console.log(blue("-".repeat(message.length)));
  console.log(`${blue(message)}`);
  console.log(blue("-".repeat(message.length)));
  line();
};

/**
 * Log an error message and exit
 * @param {string} message
 * @param {...any} rest
 */
export const error = (message, ...rest) => {
  line();
  console.log(` ❌ ${red(bold(`${message}`))}`, ...rest);
  exit(1);
};

/**
 * Log a line
 */
export const line = () => console.log("");

/**
 * Read a file, fall back to undefined if it doesn't exist
 * @param {string} path
 */
export function readFile(path) {
  return existsSync(path)
    ? readFileSync(path, { encoding: "utf-8" })
    : undefined;
}

/**
 * Get infos from the composer.json
 * @return {{ packageName: string, dependencies: Record<string, string>, platform: Record<string, string> }}
 */
export function getInfosFromComposerJSON() {
  const json = JSON.parse(readFileSync(path.join(cwd(), "composer.json"), "utf8")); // prettier-ignore
  const fullName = json.name;

  if (!fullName) {
    throw new Error(`No name found in composer.json`);
  }
  if (!fullName.includes("/")) {
    throw new Error(
      `Invalid name found in composer.json. It must be 'vendor-name/package-name'`,
    );
  }

  return {
    packageName: fullName.split("/")[1],
    dependencies: json["require"] || {},
    platform: json["config"]?.platform || {},
  };
}

/**
 * Get the path to the release build folder
 */
export function getBuildFolder() {
  return `build/${getInfosFromComposerJSON().packageName}`;
}

/**
 * Run `fn` only when its module is the entry point, so that a command can also be
 * imported by another one. Commands resolve paths against the cwd, hence the guard.
 * @param {string} moduleUrl the caller's `import.meta.url`
 * @param {() => unknown} fn
 */
export function runAsScript(moduleUrl, fn) {
  if (fileURLToPath(moduleUrl) !== process.argv[1]) {
    return;
  }

  if (
    !existsSync(resolve(cwd(), "package.json")) ||
    !existsSync(resolve(cwd(), "composer.json"))
  ) {
    error(`Must be executed from the package root directory`);
  }

  Promise.resolve()
    .then(fn)
    .catch((thrown) => {
      console.error(thrown);
      exit(1);
    });
}
