import fs from "fs";
import path from "path";

// Read the version and name from package.json
const packageJsonPath = path.join(process.cwd(), "package.json");
const { version, name } = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"));

// Allow to overwrite the plugin main file via an argument
const [, , pluginFileName = `${name}.php`] = process.argv;

const pluginFilePath = path.join(process.cwd(), pluginFileName);

// // Update version in the PHP file
let pluginFile = fs.readFileSync(pluginFilePath, 'utf8');
pluginFile = pluginFile.replace(/Version:\s*\d+\.\d+\.\d+/, `Version: ${version}`);
fs.writeFileSync(pluginFilePath, pluginFile, 'utf8');

console.log(`Updated version to ${version} in ${pluginFileName}`);

/**
 * Dump and die
 */
function dd(...args) {
  console.log(...args);
  process.exit();
}
