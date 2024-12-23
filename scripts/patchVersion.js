// @ts-check
import fs from "fs";
import path from "path";

// Read the version and name from package.json
const packageJsonPath = path.join(process.cwd(), "package.json");
const { version: packageVersion, name: packageName } = JSON.parse(
  fs.readFileSync(packageJsonPath, "utf8"),
);

// Allow to overwrite the plugin main file via an argument
const [, , pluginFileName = `${packageName}.php`] = process.argv;

const pluginFilePath = path.join(process.cwd(), pluginFileName);

// Bail early if the file doesn't exist
if (!fs.existsSync(pluginFilePath)) {
  console.error(`❌ plugin file not found: ${pluginFileName}`);
  process.exit(1);
}

// Update version in the main plugin PHP file
let pluginFile = fs.readFileSync(pluginFilePath, "utf8");

const versionRegexp = /\*\s*Version:\s*(\d+\.\d+\.\d+)/;
const currentVersion = pluginFile.match(versionRegexp)?.[1];

if (!currentVersion) {
  console.error(`❌ No version found in file: ${pluginFileName}`);
  process.exit(1);
}

if (currentVersion === packageVersion) {
  console.error(`✅ Version already updated in ${pluginFileName}: ${currentVersion}`);
  process.exit(0);
}

pluginFile = pluginFile.replace(versionRegexp, `* Version: ${packageVersion}`);
fs.writeFileSync(pluginFilePath, pluginFile, "utf8");

console.log(`✅ Patched version to ${packageVersion} in ${pluginFileName}`);

/**
 * Dump and die
 * @param {...any} args
 */
function dd(...args) {
  console.log(...args);
  process.exit();
}
