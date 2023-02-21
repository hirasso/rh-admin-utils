/**
 * WebPack Config to be used with classic backend servers (e.g. WordPress, ...)
 * Copyright: Rasso Hilbr <mail@rassohilber.com>
 * License: ISC
 *
 * Features:
 *    – Transpilation of JS(+TypeScript) and SCSS
 *    – Support for dynamic imports [e.g. await import(...)]
 *    – Handling of assets (Fonts, Images)
 *    – HTTPS support
 *    – Support for css autoprefixer and logical properties
 *    – Debugging over local network (BrowserSync Proxy)
 *    – Live reloading with automatic snippet injection
 *    - Optionally watches extra files and reloads the browser if they change
 *
 * Usage: See "scripts" in package.json
 *
 */

/**
 * Imports
 */
import path from "path";
import { fileURLToPath } from "url";
import RemoveEmptyScriptsPlugin from "webpack-remove-empty-scripts";
import MiniCssExtractPlugin from "mini-css-extract-plugin";
import LiveReloadPlugin from "webpack-livereload-plugin";
import browserSync from "browser-sync";
import fs from "fs";
import chokidar from "chokidar";
import { throttle } from "lodash-es";
import * as dotenv from "dotenv";
import { ESBuildMinifyPlugin } from "esbuild-loader";
import { resolveToEsbuildTarget } from "esbuild-plugin-browserslist";
import browserslist from "browserslist";
import { BundleAnalyzerPlugin } from "webpack-bundle-analyzer";
import findup from "findup-sync";
dotenv.config({ path: findup(".env") });

/**
 * Settings
 */
const settings = {
  entryPoints: {
    "rhau-admin": ["./assets-src/rhau-admin.js"],
    "rhau-environments": ["./assets-src/rhau-environments.js"],
  },
  hostname: process.env.DEV_HOST,
  key: `/Applications/MAMP/Library/OpenSSL/certs/${process.env.DEV_HOST}.key`,
  cert: `/Applications/MAMP/Library/OpenSSL/certs/${process.env.DEV_HOST}.crt`,
  outputPath: "assets",
  watchExtraFiles: "**/**.php",
  /**
   * Switched from `es2017` to browserslist:
   * @see https://github.com/nihalgonsalves/esbuild-plugin-browserslist
   */
  target: resolveToEsbuildTarget(browserslist(), {
    printUnknownTargets: false,
  }), // Alpine.js requires at least es2017
};

/**
 * Live Reloading
 */
const initLiveReloadPlugin = () => {
  const options = {
    key: fs.readFileSync(settings.key),
    cert: fs.readFileSync(settings.cert),
    useSourceHash: true,
    appendScriptTag: true,
  };
  const liveReloadPlugin = new LiveReloadPlugin(options);

  if (settings.watchExtraFiles) attachFileWatcher(liveReloadPlugin);

  return liveReloadPlugin;
};

/**
 * Attach a file watcher for extra files to the liveReloadPlugin
 */
const attachFileWatcher = (liveReloadPlugin) => {
  const reload = (path) => {
    liveReloadPlugin.logger.info(`${path} changed, reloading...`);
    // this goes through to tiny-lr that is being used by LiveReloadPlugin
    liveReloadPlugin.server.notifyClients([path]);
  };
  const watcher = chokidar.watch(settings.watchExtraFiles, {
    ignored: ["node_modules", "vendor"],
    ignoreInitial: true,
    followSymlinks: false,
    atomic: false,
  });
  watcher
    .on("change", throttle(reload, 300))
    .on("unlink", throttle(reload, 300));
};

/**
 * Init BrowserSync for local mobile debugging
 */
const initBrowserSync = () => {
  let url = `https://${settings.hostname}`;
  url += settings.publicPath || "";
  const options = {
    proxy: url,
    ui: false,
    port: 12345,
    ghostMode: false,
    open: false,
    notify: false,
    injectChanges: false,
  };
  return browserSync.create().init(options);
};

/**
 * Setup the config
 */
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

let config = {
  entry: settings.entryPoints,
  output: {
    publicPath: "auto",
    path: path.resolve(__dirname, settings.outputPath),
    clean: true,
    filename: "[name].js",
    assetModuleFilename: "[name][ext][query]",
    chunkFilename: "[name].chunk.[contenthash].js",
  },
  module: {
    parser: {
      javascript: {
        importMeta: false,
      },
    },
    rules: [
      {
        test: /\.(js|ts)x?$/,
        loader: "esbuild-loader",
        // exclude: /(node_modules)/,
        options: {
          loader: "ts",
          target: settings.target,
        },
      },
      {
        test: /\.(scss|css)$/,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          {
            loader: "postcss-loader",
            options: {
              postcssOptions: {
                plugins: [
                  [
                    "postcss-preset-env",
                    {
                      // Options
                    },
                  ],
                ],
              },
            },
          },
          "sass-loader",
        ],
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        type: "asset/resource",
      },
      {
        test: /.(ttf|otf|eot|woff(2)?)$/,
        type: "asset/resource",
        generator: {
          filename: "[name][ext]",
        },
      },
      {
        test: /\/static\//,
        type: "asset/resource",
        generator: {
          filename: "static/[name][ext]",
        },
      },
    ],
  },
  resolve: {
    extensions: [".tsx", ".ts", ".js"],
  },
  optimization: {
    usedExports: true,
    minimizer: [
      new ESBuildMinifyPlugin({
        target: settings.target,
      }),
    ],
  },
  plugins: [
    // new BundleAnalyzerPlugin(),
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: "[name].css",
    }),
  ],
};

/**
 * Adjust the config depending on the --mode
 * @see https://webpack.js.org/configuration/mode/
 * @param {object} env  set via --env flag
 * @param {object} argv
 * @returns
 */
export default (env, argv) => {
  config.mode = argv.mode;

  if (argv.mode === "development") {
    config.devtool = "cheap-source-map";
    initBrowserSync();
    config.plugins.push(initLiveReloadPlugin());
  }

  return config;
};
