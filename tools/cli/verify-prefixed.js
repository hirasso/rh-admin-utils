#!/usr/bin/env node

import { readFileSync } from "node:fs";
import path from "node:path";
import { execSync } from "node:child_process";
import { cwd } from "node:process";
import fg from "fast-glob";

import {
  blue,
  error,
  info,
  readFile,
  run,
  runAsScript,
  success,
} from "./lib.js";

/** Regenerated with a different value on every commit, and read by nothing */
const VOLATILE_PREFIXED_FILE = "vendor-prefixed/composer/installed.php";

/**
 * Verify vendor-prefixed/ matches composer.lock and that prefixing covered every symbol.
 * Committed build output drifts silently, hence the check in CI.
 */
export async function verifyPrefixedDependencies() {
  const { prefixedNamespaces, namespacePrefix } = getStraussConfig();

  info("Regenerating the prefixed dependencies...");
  run("composer prefix --quiet");

  /**
   * installed.php embeds the root package's commit SHA, so it changes on every commit
   * and could never match what is committed. Nothing reads it, so restore it.
   */
  run(`git checkout -- ${VOLATILE_PREFIXED_FILE} 2>/dev/null || true`);

  /** Against the index, not `git status`: staged-but-uncommitted still counts as in sync */
  const capture = (command) => execSync(command, { encoding: "utf-8" }).trim();

  const changed = [
    capture("git diff --name-only -- vendor-prefixed"),
    capture("git ls-files --others --exclude-standard -- vendor-prefixed"),
  ]
    .filter(Boolean)
    .join("\n");

  if (changed.length) {
    error(
      `vendor-prefixed/ is out of sync with composer.lock. Run ${blue("composer prefix")} and commit the result:`,
      `\n${changed}`,
    );
  }

  success("vendor-prefixed/ is in sync with composer.lock");

  /** Catches what a static rewriter misses: dynamic class names, names in strings */
  info("Checking that no unprefixed namespaces survived...");

  const files = await fg("**/*.php", {
    cwd: "vendor-prefixed",
    absolute: true,
  });
  const leaked = [];

  for (const file of files) {
    /** Normalise escaped backslashes to match names inside strings too */
    const contents = readFileSync(file, "utf-8").replaceAll("\\\\", "\\");

    for (const prefixedNamespace of prefixedNamespaces) {
      const pattern = new RegExp(
        `(?<!${escapeRegExp(`${namespacePrefix}\\`)})\\b${escapeRegExp(prefixedNamespace)}\\\\`,
      );
      if (pattern.test(contents)) {
        leaked.push(
          `  - ${prefixedNamespace} in ${path.relative(cwd(), file)}`,
        );
      }
    }
  }

  if (leaked.length) {
    error(`Unprefixed namespaces survived in vendor-prefixed/:`, `\n${leaked.join("\n")}`); // prettier-ignore
  }

  success("No unprefixed namespaces found in vendor-prefixed/");
}

/**
 * Read the prefixed namespaces from the generated autoloader rather than hardcoding them.
 * Full namespaces, not vendor segments: var-dumper imports optional integrations it was
 * never shipped with, which stay unprefixed on purpose.
 *
 * @return {{ prefixedNamespaces: string[], namespacePrefix: string }}
 */
function getStraussConfig() {
  const composerJson = JSON.parse(readFile("composer.json") || "{}");
  const strauss = composerJson.extra?.strauss ?? {};
  const namespacePrefix = (strauss.namespace_prefix ?? "").replace(/\\+$/, "");

  if (!namespacePrefix) {
    error(`No extra.strauss.namespace_prefix found in composer.json`);
  }

  const psr4 = readFile("vendor-prefixed/composer/autoload_psr4.php") || "";

  /** e.g. "RH\\AdminUtils\\Vendor\\Symfony\\Component\\VarDumper\\" => "Symfony\\Component\\VarDumper" */
  const prefixedNamespaces = [
    ...new Set(
      [...psr4.matchAll(/'((?:[A-Za-z0-9_]+\\\\)+)'\s*=>/g)]
        .map(([, ns]) => ns.replaceAll("\\\\", "\\").replace(/\\+$/, ""))
        .filter((ns) => ns.startsWith(`${namespacePrefix}\\`))
        .map((ns) => ns.slice(namespacePrefix.length + 1)),
    ),
  ];

  if (!prefixedNamespaces.length) {
    error(`No prefixed namespaces found in vendor-prefixed/composer/autoload_psr4.php`); // prettier-ignore
  }

  return { prefixedNamespaces, namespacePrefix };
}

/**
 * Escape a string for literal use inside a RegExp
 * @param {string} value
 */
const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

runAsScript(import.meta.url, verifyPrefixedDependencies);
