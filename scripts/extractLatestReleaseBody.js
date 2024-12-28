// @ts-check
import fs from "fs";
import path from "path";
import { getVersion } from "./utils.js";

const changelogPath = path.resolve("CHANGELOG.md");
const releasePath = path.resolve("RELEASE.md");
const version = getVersion();
const regex = new RegExp(`## ${version}.*?(###.*?)## \\d`, 'is');
const changelogContent = fs.readFileSync(changelogPath, "utf-8");
const match = regex.exec(changelogContent);

if (!match) {
  throw new Error("❌ Couldn't parse the body of the latest release");
}

fs.writeFileSync(releasePath, match[1], "utf-8");

console.log("✅ Wrote latest release body to RELEASE.md");
