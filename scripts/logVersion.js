// @ts-check
import * as fs from "fs";
import { fileURLToPath } from "url";
import path from "path";

// Get the directory of the current script
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Read the version and name from package.json
const packageJsonPath = path.join(__dirname, "../package.json");
const { version } = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"));
// Log the version
console.log(version ?? "");
