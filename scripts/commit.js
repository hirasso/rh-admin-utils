import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';

/**
 * Dump and die
 */
function dd(...args) {
  console.log(...args);
  process.exit();
}

/**
 * Custom commit hook for Changesets.
 * You can modify the commit process as needed.
 */
export default function commit(options) {
  // Access the custom option passed in the config
  const { customOption } = options;
  dd(options);

  // Get the current version and changelog
  const packageJsonPath = path.resolve(process.cwd(), 'package.json');
  const { version } = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

  // Build a custom commit message
  let commitMessage = `Release version ${version}`;

  // If customOption is true, add extra info to the commit message
  if (customOption) {
    commitMessage += ' (including some custom changes)';
  }

  // Perform the commit using Git
  execSync('git add .'); // Add all files to the commit
  execSync(`git commit -m "${commitMessage}"`); // Commit with the custom message
  execSync('git push'); // Optionally, push the commit
}
