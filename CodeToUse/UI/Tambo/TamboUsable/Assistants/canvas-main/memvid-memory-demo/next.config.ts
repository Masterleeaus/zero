import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Monorepo: bundle/transpile our workspace packages so Next doesn't externalize them
  // (externalized ESM + CJS require() => ERR_PACKAGE_PATH_NOT_EXPORTED / ERR_REQUIRE_ESM)
  transpilePackages: ['@memvid/canvas-core', '@memvid/canvas-server', '@memvid/canvas-react'],

  // Required for native modules like @memvid/sdk and pdf-parse dependencies
  serverExternalPackages: [
    '@memvid/sdk',
    'pdf-parse',
    '@napi-rs/canvas',
    '@napi-rs/canvas-darwin-arm64',
    '@napi-rs/canvas-darwin-x64',
    '@napi-rs/canvas-linux-x64-gnu',
    '@napi-rs/canvas-win32-x64-msvc',
    'canvas',
    'pdfjs-dist',
    'officeparser',
    'mammoth',
    'xlsx',
    'unpdf',
  ],

  // Configure webpack for native modules
  webpack: (config, { isServer }) => {
    if (isServer) {
      // Externalize native modules on the server
      config.externals = config.externals || [];
      config.externals.push('@memvid/sdk');
      config.externals.push('pdf-parse');
      config.externals.push(/^@napi-rs\/canvas.*/);
      config.externals.push('canvas');
      config.externals.push('officeparser');
      config.externals.push('mammoth');
      config.externals.push('xlsx');
      config.externals.push('unpdf');
      config.externals.push('pdfjs-dist');
    } else {
      // Client-side: exclude Node.js built-ins
      config.resolve.fallback = {
        ...config.resolve.fallback,
        fs: false,
        path: false,
        os: false,
        url: false,
        util: false,
      };
    }
    return config;
  },
};

export default nextConfig;
