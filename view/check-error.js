  vite:config         preserveSymlinks: false,
  vite:config         alias: [
  vite:config           {
  vite:config             find: '@',
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src'
  vite:config           },
  vite:config           {
  vite:config             find: '@images',
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/assets/images'
  vite:config           },
  vite:config           { find: '@assets', replacement: '/src/assets' },
  vite:config           { find: 'randombytes', replacement: 'randombytes/browser' },
  vite:config           {
  vite:config             find: /^astro$/,
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/astro/dist/types/public/index.js'
  vite:config           },
  vite:config           {
  vite:config             find: 'astro:middleware',
  vite:config             replacement: 'astro/virtual-modules/middleware.js'
  vite:config           },
  vite:config           { find: 'astro:schema', replacement: 'astro/zod' },
  vite:config           { find: 'astro:components', replacement: 'astro/components' },
  vite:config           {
  vite:config             find: /^\/?@vite\/env/,
  vite:config             replacement: '/@fs/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/vite/dist/client/env.mjs'
  vite:config           },
  vite:config           {
  vite:config             find: /^\/?@vite\/client/,
  vite:config             replacement: '/@fs/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/vite/dist/client/client.mjs'
  vite:config           }
  vite:config         ],
  vite:config         mainFields: [ 'browser', 'module', 'jsnext:main', 'jsnext' ],
  vite:config         conditions: [ 'module', 'browser', 'development|production', 'astro' ],
  vite:config         builtins: []
  vite:config       },
  vite:config       keepProcessEnv: false,
  vite:config       consumer: 'client',
  vite:config       optimizeDeps: {
  vite:config         include: [
  vite:config           '@astrojs/react/client.js',
  vite:config           'react',
  vite:config           'react-dom',
  vite:config           'react/jsx-dev-runtime',
  vite:config           'react/jsx-runtime',
  vite:config           'astro > cssesc',
  vite:config           'astro > aria-query',
  vite:config           'astro > axobject-query'
  vite:config         ],
  vite:config         exclude: [ 'astro', 'node-fetch', '@astrojs/react/server.js' ],
  vite:config         needsInterop: [],
  vite:config         extensions: [],
  vite:config         disabled: undefined,
  vite:config         holdUntilCrawlEnd: true,
  vite:config         force: false,
  vite:config         noDiscovery: false,
  vite:config         esbuildOptions: { preserveSymlinks: false, jsx: 'automatic' },
  vite:config         entries: [
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/**/*.{jsx,tsx,vue,svelte,html,astro}'
  vite:config         ]
  vite:config       },
  vite:config       dev: {
  vite:config         warmup: [],
  vite:config         sourcemap: { js: true },
  vite:config         sourcemapIgnoreList: [Function: isInNodeModules$1],
  vite:config         preTransformRequests: true,
  vite:config         createEnvironment: [Function: defaultCreateClientDevEnvironment],
  vite:config         recoverable: true,
  vite:config         moduleRunnerTransform: false
  vite:config       },
  vite:config       build: {
  vite:config         target: 'esnext',
  vite:config         polyfillModulePreload: true,
  vite:config         modulePreload: { polyfill: true },
  vite:config         outDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/dist/',
  vite:config         assetsDir: '_astro',
  vite:config         assetsInlineLimit: 0,
  vite:config         sourcemap: false,
  vite:config         terserOptions: {},
  vite:config         rollupOptions: {
  vite:config           input: [
  vite:config             '@astrojs/react/client.js',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/en/thanks.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/gracias.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselSwitch.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/Nav.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselDesktop.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselMobile.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNav.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/faq/accordion/Accordion.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/starwind/dropdown/Dropdown.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/language-select/MobileLanguageSelect.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNavDropdown.astro?astro&type=script&index=0&lang.ts'
  vite:config           ],
  vite:config           output: {
  vite:config             format: 'esm',
  vite:config             entryFileNames: '_astro/[name].[hash].js',
  vite:config             chunkFileNames: '_astro/[name].[hash].js',
  vite:config             assetFileNames: '_astro/[name].[hash][extname]'
  vite:config           },
  vite:config           preserveEntrySignatures: 'exports-only',
  vite:config           onwarn: [Function: onwarn]
  vite:config         },
  vite:config         commonjsOptions: { include: [ /node_modules/ ], extensions: [ '.js', '.cjs' ] },
  vite:config         dynamicImportVarsOptions: { warnOnError: true, exclude: [ /node_modules/ ] },
  vite:config         write: true,
  vite:config         emptyOutDir: false,
  vite:config         copyPublicDir: false,
  vite:config         manifest: false,
  vite:config         lib: false,
  vite:config         ssrManifest: false,
  vite:config         ssrEmitAssets: false,
  vite:config         reportCompressedSize: true,
  vite:config         chunkSizeWarningLimit: 500,
  vite:config         watch: null,
  vite:config         cssCodeSplit: true,
  vite:config         minify: 'esbuild',
  vite:config         ssr: false,
  vite:config         emitAssets: true,
  vite:config         createEnvironment: [Function: createEnvironment],
  vite:config         cssTarget: 'esnext',
  vite:config         cssMinify: true
  vite:config       }
  vite:config     },
  vite:config     ssr: {
  vite:config       define: {
  vite:config         'import.meta.env.SITE': '"https://neomaxilo.com"',
  vite:config         'import.meta.env.BASE_URL': '"/"',
  vite:config         'import.meta.env.ASSETS_PREFIX': 'undefined',
  vite:config         __ASTRO_INTERNAL_I18N_CONFIG__: '{"base":"/","format":"directory","site":"https://neomaxilo.com","trailingSlash":"ignore","i18n":{"defaultLocale":"es","locales":["en","es"],"routing":{"prefixDefaultLocale":false,"redirectToDefaultLocale":true,"fallbackType":"redirect"}},"isBuild":true}'
  vite:config       },
  vite:config       resolve: {
  vite:config         externalConditions: [ 'node' ],
  vite:config         extensions: [
  vite:config           '.mjs',  '.js',
  vite:config           '.mts',  '.ts',
  vite:config           '.jsx',  '.tsx',
  vite:config           '.json'
  vite:config         ],
  vite:config         dedupe: [ 'astro', 'react', 'react-dom' ],
  vite:config         noExternal: [
  vite:config           'astro',
  vite:config           'astro/components',
  vite:config           '@nanostores/preact',
  vite:config           '@fontsource/*',
  vite:config           '@astrojs/mdx',
  vite:config           '@astrojs/react',
  vite:config           '@astrojs/sitemap',
  vite:config           'astro-auto-import',
  vite:config           'astro-icon',
  vite:config           'astro-seo',
  vite:config           '@playform/compress',
  vite:config           'astro-robots-txt',
  vite:config           '@mui/material',
  vite:config           '@mui/base',
  vite:config           '@babel/runtime',
  vite:config           'use-immer',
  vite:config           '@material-tailwind/react'
  vite:config         ],
  vite:config         external: [ 'stream', 'util', 'os', 'fs', 'svgo', '@iconify-json/*' ],
  vite:config         preserveSymlinks: false,
  vite:config         alias: [
  vite:config           {
  vite:config             find: '@',
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src'
  vite:config           },
  vite:config           {
  vite:config             find: '@images',
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/assets/images'
  vite:config           },
  vite:config           { find: '@assets', replacement: '/src/assets' },
  vite:config           { find: 'randombytes', replacement: 'randombytes/browser' },
  vite:config           {
  vite:config             find: /^astro$/,
  vite:config             replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/astro/dist/types/public/index.js'
  vite:config           },
  vite:config           {
  vite:config             find: 'astro:middleware',
  vite:config             replacement: 'astro/virtual-modules/middleware.js'
  vite:config           },
  vite:config           { find: 'astro:schema', replacement: 'astro/zod' },
  vite:config           { find: 'astro:components', replacement: 'astro/components' },
  vite:config           {
  vite:config             find: /^\/?@vite\/env/,
  vite:config             replacement: '/@fs/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/vite/dist/client/env.mjs'
  vite:config           },
  vite:config           {
  vite:config             find: /^\/?@vite\/client/,
  vite:config             replacement: '/@fs/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/vite/dist/client/client.mjs'
  vite:config           }
  vite:config         ],
  vite:config         mainFields: [ 'module', 'jsnext:main', 'jsnext' ],
  vite:config         conditions: [ 'module', 'node', 'development|production', 'astro' ],
  vite:config         builtins: [
  vite:config           '_http_agent',         '_http_client',        '_http_common',
  vite:config           '_http_incoming',      '_http_outgoing',      '_http_server',
  vite:config           '_stream_duplex',      '_stream_passthrough', '_stream_readable',
  vite:config           '_stream_transform',   '_stream_wrap',        '_stream_writable',
  vite:config           '_tls_common',         '_tls_wrap',           'assert',
  vite:config           'assert/strict',       'async_hooks',         'buffer',
  vite:config           'child_process',       'cluster',             'console',
  vite:config           'constants',           'crypto',              'dgram',
  vite:config           'diagnostics_channel', 'dns',                 'dns/promises',
  vite:config           'domain',              'events',              'fs',
  vite:config           'fs/promises',         'http',                'http2',
  vite:config           'https',               'inspector',           'inspector/promises',
  vite:config           'module',              'net',                 'os',
  vite:config           'path',                'path/posix',          'path/win32',
  vite:config           'perf_hooks',          'process',             'punycode',
  vite:config           'querystring',         'readline',            'readline/promises',
  vite:config           'repl',                'stream',              'stream/consumers',
  vite:config           'stream/promises',     'stream/web',          'string_decoder',
  vite:config           'sys',                 'timers',              'timers/promises',
  vite:config           'tls',                 'trace_events',        'tty',
  vite:config           'url',                 'util',                'util/types',
  vite:config           'v8',                  'vm',                  'wasi',
  vite:config           'worker_threads',      'zlib',                /^node:/,
  vite:config           /^npm:/,               /^bun:/
  vite:config         ]
  vite:config       },
  vite:config       keepProcessEnv: true,
  vite:config       consumer: 'server',
  vite:config       optimizeDeps: {
  vite:config         include: [],
  vite:config         exclude: [],
  vite:config         needsInterop: [],
  vite:config         extensions: [],
  vite:config         disabled: undefined,
  vite:config         holdUntilCrawlEnd: true,
  vite:config         force: false,
  vite:config         noDiscovery: true,
  vite:config         esbuildOptions: { preserveSymlinks: false }
  vite:config       },
  vite:config       dev: {
  vite:config         warmup: [],
  vite:config         sourcemap: { js: true },
  vite:config         sourcemapIgnoreList: [Function: isInNodeModules$1],
  vite:config         preTransformRequests: false,
  vite:config         createEnvironment: [Function: defaultCreateDevEnvironment],
  vite:config         recoverable: false,
  vite:config         moduleRunnerTransform: true
  vite:config       },
  vite:config       build: {
  vite:config         target: 'esnext',
  vite:config         polyfillModulePreload: true,
  vite:config         modulePreload: { polyfill: true },
  vite:config         outDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/dist/',
  vite:config         assetsDir: '_astro',
  vite:config         assetsInlineLimit: 0,
  vite:config         sourcemap: false,
  vite:config         terserOptions: {},
  vite:config         rollupOptions: {
  vite:config           input: [
  vite:config             '@astrojs/react/client.js',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/en/thanks.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/gracias.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselSwitch.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/Nav.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselDesktop.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselMobile.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNav.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/faq/accordion/Accordion.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/starwind/dropdown/Dropdown.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/language-select/MobileLanguageSelect.astro?astro&type=script&index=0&lang.ts',
  vite:config             '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNavDropdown.astro?astro&type=script&index=0&lang.ts'
  vite:config           ],
  vite:config           output: {
  vite:config             format: 'esm',
  vite:config             entryFileNames: '_astro/[name].[hash].js',
  vite:config             chunkFileNames: '_astro/[name].[hash].js',
  vite:config             assetFileNames: '_astro/[name].[hash][extname]'
  vite:config           },
  vite:config           preserveEntrySignatures: 'exports-only',
  vite:config           onwarn: [Function: onwarn]
  vite:config         },
  vite:config         commonjsOptions: { include: [ /node_modules/ ], extensions: [ '.js', '.cjs' ] },
  vite:config         dynamicImportVarsOptions: { warnOnError: true, exclude: [ /node_modules/ ] },
  vite:config         write: true,
  vite:config         emptyOutDir: false,
  vite:config         copyPublicDir: false,
  vite:config         manifest: false,
  vite:config         lib: false,
  vite:config         ssrManifest: false,
  vite:config         ssrEmitAssets: false,
  vite:config         reportCompressedSize: true,
  vite:config         chunkSizeWarningLimit: 500,
  vite:config         watch: null,
  vite:config         cssCodeSplit: true,
  vite:config         minify: false,
  vite:config         ssr: true,
  vite:config         emitAssets: true,
  vite:config         createEnvironment: [Function: createEnvironment],
  vite:config         cssTarget: 'esnext',
  vite:config         cssMinify: 'esbuild'
  vite:config       }
  vite:config     }
  vite:config   },
  vite:config   configFileDependencies: [],
  vite:config   inlineConfig: {
  vite:config     configFile: false,
  vite:config     mode: 'production',
  vite:config     cacheDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/.vite/',
  vite:config     clearScreen: false,
  vite:config     customLogger: {
  vite:config       hasWarned: false,
  vite:config       info: [Function: info],
  vite:config       warn: [Function: warn],
  vite:config       warnOnce: [Function: warnOnce],
  vite:config       error: [Function: error],
  vite:config       clearScreen: [Function: clearScreen],
  vite:config       hasErrorLogged: [Function: hasErrorLogged]
  vite:config     },
  vite:config     appType: 'custom',
  vite:config     optimizeDeps: {
  vite:config       entries: [
  vite:config         '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/**/*.{jsx,tsx,vue,svelte,html,astro}'
  vite:config       ],
  vite:config       exclude: [ 'astro', 'node-fetch', '@astrojs/react/server.js' ],
  vite:config       include: [ '@astrojs/react/client.js' ]
  vite:config     },
  vite:config     plugins: [
  vite:config       {
  vite:config         name: '@astro/plugin-component-entry',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [AsyncFunction: load]
  vite:config       },
  vite:config       {
  vite:config         name: '@astro/plugin-build-internals',
  vite:config         config: [Function: config],
  vite:config         generateBundle: [AsyncFunction: generateBundle]
  vite:config       },
  vite:config       [
  vite:config         {
  vite:config           name: 'astro:rollup-plugin-build-css',
  vite:config           outputOptions: [Function: outputOptions],
  vite:config           generateBundle: [AsyncFunction: generateBundle]
  vite:config         },
  vite:config         {
  vite:config           name: 'astro:rollup-plugin-single-css',
  vite:config           enforce: 'post',
  vite:config           configResolved: [Function: configResolved],
  vite:config           generateBundle: [Function: generateBundle]
  vite:config         },
  vite:config         {
  vite:config           name: 'astro:rollup-plugin-inline-stylesheets',
  vite:config           enforce: 'post',
  vite:config           configResolved: [Function: configResolved],
  vite:config           generateBundle: [AsyncFunction: generateBundle]
  vite:config         }
  vite:config       ],
  vite:config       {
  vite:config         name: '@astro/plugin-scripts',
  vite:config         configResolved: [Function: configResolved],
  vite:config         generateBundle: [AsyncFunction: generateBundle]
  vite:config       },
  vite:config       {
  vite:config         enforce: 'pre',
  vite:config         name: 'astro-manifest-plugin',
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [Function: load]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:tsconfig-alias',
  vite:config         enforce: 'post',
  vite:config         configResolved: [Function: configResolved],
  vite:config         resolveId: [AsyncFunction: resolveId]
  vite:config       },
  vite:config       false,
  vite:config       [
  vite:config         {
  vite:config           name: 'astro:build',
  vite:config           enforce: 'pre',
  vite:config           configEnvironment: [AsyncFunction: configEnvironment],
  vite:config           configResolved: [Function: configResolved],
  vite:config           configureServer: [Function: configureServer],
  vite:config           buildStart: [Function: buildStart],
  vite:config           load: [AsyncFunction: load],
  vite:config           transform: [AsyncFunction: transform],
  vite:config           handleHotUpdate: [AsyncFunction: handleHotUpdate]
  vite:config         },
  vite:config         {
  vite:config           name: 'astro:build:normal',
  vite:config           resolveId: [Function: resolveId]
  vite:config         }
  vite:config       ],
  vite:config       {
  vite:config         name: 'astro:scripts',
  vite:config         config: [Function: config],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [AsyncFunction: load],
  vite:config         buildStart: [Function: buildStart]
  vite:config       },
  vite:config       false,
  vite:config       {
  vite:config         name: 'astro:vite-plugin-env',
  vite:config         config: [Function: config],
  vite:config         configResolved: [Function: configResolved],
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro-env-plugin',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         buildStart: [Function: buildStart],
  vite:config         buildEnd: [Function: buildEnd],
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [Function: load]
  vite:config       },
  vite:config       {
  vite:config         enforce: 'pre',
  vite:config         name: 'astro:markdown',
  vite:config         buildEnd: [Function: buildEnd],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [AsyncFunction: load]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:html',
  vite:config         options: [Function: options],
  vite:config         transform: [AsyncFunction: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:postprocess',
  vite:config         transform: [AsyncFunction: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:integration-container',
  vite:config         configureServer: [AsyncFunction: configureServer],
  vite:config         buildStart: [AsyncFunction: buildStart]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:scripts:page-ssr',
  vite:config         enforce: 'post',
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:head-metadata',
  vite:config         enforce: 'pre',
  vite:config         apply: 'serve',
  vite:config         configureServer: [Function: configureServer],
  vite:config         resolveId: [Function: resolveId],
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:scanner',
  vite:config         enforce: 'post',
  vite:config         transform: [AsyncFunction: transform],
  vite:config         handleHotUpdate: [AsyncFunction: handleHotUpdate]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro-content-virtual-mod-plugin',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         buildStart: [Function: buildStart],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [AsyncFunction: load],
  vite:config         configureServer: [Function: configureServer]
  vite:config       },
  vite:config       [
  vite:config         {
  vite:config           name: 'astro:content-imports',
  vite:config           config: [Function: config],
  vite:config           buildStart: [AsyncFunction: buildStart],
  vite:config           transform: [AsyncFunction: transform],
  vite:config           configureServer: [Function: configureServer]
  vite:config         }
  vite:config       ],
  vite:config       {
  vite:config         name: 'astro:content-asset-propagation',
  vite:config         enforce: 'pre',
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         configureServer: [Function: configureServer],
  vite:config         transform: [AsyncFunction: transform]
  vite:config       },
  vite:config       {
  vite:config         name: '@astro/plugin-middleware',
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [AsyncFunction: load]
  vite:config       },
  vite:config       {
  vite:config         name: '@astrojs/vite-plugin-astro-ssr-manifest',
  vite:config         enforce: 'post',
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [Function: load]
  vite:config       },
  vite:config       [
  vite:config         {
  vite:config           name: 'astro:assets',
  vite:config           config: [Function: config],
  vite:config           resolveId: [AsyncFunction: resolveId],
  vite:config           load: [Function: load],
  vite:config           buildStart: [Function: buildStart],
  vite:config           renderChunk: [AsyncFunction: renderChunk]
  vite:config         },
  vite:config         {
  vite:config           name: 'astro:assets:esm',
  vite:config           enforce: 'pre',
  vite:config           config: [Function: config],
  vite:config           configResolved: [Function: configResolved],
  vite:config           load: [AsyncFunction: load]
  vite:config         },
  vite:config         {
  vite:config           name: 'astro:fonts:fallback',
  vite:config           resolveId: [Function: resolveId],
  vite:config           load: [Function: load]
  vite:config         }
  vite:config       ],
  vite:config       {
  vite:config         name: 'astro:prefetch',
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [Function: load],
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:transitions',
  vite:config         config: [Function: config],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [Function: load],
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:dev-toolbar',
  vite:config         config: [Function: config],
  vite:config         resolveId: [Function: resolveId],
  vite:config         configureServer: [Function: configureServer],
  vite:config         load: [AsyncFunction: load]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:vite-plugin-file-url',
  vite:config         enforce: 'pre',
  vite:config         resolveId: [Function: resolveId]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:i18n',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         resolveId: [Function: resolveId]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:actions',
  vite:config         enforce: 'pre',
  vite:config         resolveId: [Function: resolveId],
  vite:config         configureServer: [AsyncFunction: configureServer],
  vite:config         load: [AsyncFunction: load]
  vite:config       },
  vite:config       {
  vite:config         name: '@astro/plugin-actions',
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         load: [Function: load]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:server-islands',
  vite:config         enforce: 'post',
  vite:config         config: [Function: config],
  vite:config         configureServer: [Function: configureServer],
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [Function: load],
  vite:config         transform: [Function: transform],
  vite:config         renderChunk: [Function: renderChunk]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:container',
  vite:config         enforce: 'pre',
  vite:config         resolveId: [Function: resolveId]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro:hmr-reload',
  vite:config         enforce: 'post',
  vite:config         hotUpdate: { order: 'post', handler: [Function: handler] }
  vite:config       },
  vite:config       {
  vite:config         name: '@tailwindcss/vite:scan',
  vite:config         enforce: 'pre',
  vite:config         configureServer: [Function: configureServer],
  vite:config         configResolved: [AsyncFunction: configResolved]
  vite:config       },
  vite:config       {
  vite:config         name: '@tailwindcss/vite:generate:build',
  vite:config         apply: 'build',
  vite:config         enforce: 'pre',
  vite:config         transform: [AsyncFunction: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'astro-icon',
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [AsyncFunction: load],
  vite:config         configureServer: [Function: configureServer]
  vite:config       },
  vite:config       {
  vite:config         name: '@mdx-js/rollup',
  vite:config         enforce: 'pre',
  vite:config         buildEnd: [Function: buildEnd],
  vite:config         configResolved: [Function: configResolved],
  vite:config         resolveId: [AsyncFunction: resolveId],
  vite:config         transform: [AsyncFunction: transform]
  vite:config       },
  vite:config       {
  vite:config         name: '@astrojs/mdx-postprocess',
  vite:config         transform: [Function: transform]
  vite:config       },
  vite:config       {
  vite:config         name: 'vite:react-babel',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         configResolved: [Function: configResolved],
  vite:config         options: [Function: options]
  vite:config       },
  vite:config       {
  vite:config         name: 'vite:react-refresh',
  vite:config         enforce: 'pre',
  vite:config         config: [Function: config],
  vite:config         resolveId: {
  vite:config           filter: { id: /^\/@react\-refresh$/ },
  vite:config           handler: [Function: handler]
  vite:config         },
  vite:config         load: {
  vite:config           filter: { id: /^\/@react\-refresh$/ },
  vite:config           handler: [Function: handler]
  vite:config         },
  vite:config         transformIndexHtml: [Function: transformIndexHtml]
  vite:config       },
  vite:config       {
  vite:config         name: '@astrojs/react:opts',
  vite:config         resolveId: [Function: resolveId],
  vite:config         load: [Function: load]
  vite:config       }
  vite:config     ],
  vite:config     publicDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/public/',
  vite:config     root: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/',
  vite:config     envPrefix: 'PUBLIC_',
  vite:config     define: {
  vite:config       'import.meta.env.SITE': '"https://neomaxilo.com"',
  vite:config       'import.meta.env.BASE_URL': '"/"',
  vite:config       'import.meta.env.ASSETS_PREFIX': 'undefined'
  vite:config     },
  vite:config     server: { hmr: false, watch: { ignored: [ '**' ] }, middlewareMode: true },
  vite:config     resolve: {
  vite:config       alias: [
  vite:config         {
  vite:config           find: '@',
  vite:config           replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src'
  vite:config         },
  vite:config         {
  vite:config           find: '@images',
  vite:config           replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/assets/images'
  vite:config         },
  vite:config         { find: '@assets', replacement: '/src/assets' },
  vite:config         { find: 'randombytes', replacement: 'randombytes/browser' },
  vite:config         {
  vite:config           find: /^astro$/,
  vite:config           replacement: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/astro/dist/types/public/index.js'
  vite:config         },
  vite:config         {
  vite:config           find: 'astro:middleware',
  vite:config           replacement: 'astro/virtual-modules/middleware.js'
  vite:config         },
  vite:config         { find: 'astro:schema', replacement: 'astro/zod' },
  vite:config         { find: 'astro:components', replacement: 'astro/components' }
  vite:config       ],
  vite:config       dedupe: [ 'astro' ]
  vite:config     },
  vite:config     ssr: {
  vite:config       noExternal: [
  vite:config         'astro',
  vite:config         'astro/components',
  vite:config         '@nanostores/preact',
  vite:config         '@fontsource/*',
  vite:config         '@astrojs/mdx',
  vite:config         '@astrojs/react',
  vite:config         '@astrojs/sitemap',
  vite:config         'astro-auto-import',
  vite:config         'astro-icon',
  vite:config         'astro-seo',
  vite:config         '@playform/compress',
  vite:config         'astro-robots-txt',
  vite:config         '@mui/material',
  vite:config         '@mui/base',
  vite:config         '@babel/runtime',
  vite:config         'use-immer',
  vite:config         '@material-tailwind/react'
  vite:config       ],
  vite:config       external: [ 'stream', 'util', 'os', 'fs', 'svgo', '@iconify-json/*' ]
  vite:config     },
  vite:config     build: {
  vite:config       target: 'esnext',
  vite:config       assetsDir: '_astro',
  vite:config       assetsInlineLimit: 0,
  vite:config       emptyOutDir: false,
  vite:config       outDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/dist/',
  vite:config       copyPublicDir: false,
  vite:config       rollupOptions: {
  vite:config         input: [
  vite:config           '@astrojs/react/client.js',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/en/thanks.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/pages/gracias.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselSwitch.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/Nav.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselDesktop.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/hero/HeroCarruselMobile.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNav.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/faq/accordion/Accordion.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/starwind/dropdown/Dropdown.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/language-select/MobileLanguageSelect.astro?astro&type=script&index=0&lang.ts',
  vite:config           '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/src/components/nav/mobile-nav/MobileNavDropdown.astro?astro&type=script&index=0&lang.ts'
  vite:config         ],
  vite:config         output: {
  vite:config           format: 'esm',
  vite:config           entryFileNames: '_astro/[name].[hash].js',
  vite:config           chunkFileNames: '_astro/[name].[hash].js',
  vite:config           assetFileNames: '_astro/[name].[hash][extname]'
  vite:config         },
  vite:config         preserveEntrySignatures: 'exports-only'
  vite:config       }
  vite:config     },
  vite:config     base: '/'
  vite:config   },
  vite:config   decodedBase: '/',
  vite:config   rawBase: '/',
  vite:config   command: 'build',
  vite:config   isWorker: false,
  vite:config   mainConfig: null,
  vite:config   bundleChain: [],
  vite:config   isProduction: true,
  vite:config   css: {
  vite:config     transformer: 'postcss',
  vite:config     preprocessorMaxWorkers: 0,
  vite:config     devSourcemap: false
  vite:config   },
  vite:config   json: { namedExports: true, stringify: 'auto' },
  vite:config   builder: undefined,
  vite:config   preview: {
  vite:config     port: 4173,
  vite:config     strictPort: false,
  vite:config     host: undefined,
  vite:config     allowedHosts: [],
  vite:config     https: undefined,
  vite:config     open: false,
  vite:config     proxy: undefined,
  vite:config     cors: {
  vite:config       origin: /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/
  vite:config     },
  vite:config     headers: {}
  vite:config   },
  vite:config   envDir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo',
  vite:config   env: { BASE_URL: '/', MODE: 'production', DEV: false, PROD: true },
  vite:config   assetsInclude: [Function: assetsInclude],
  vite:config   logger: {
  vite:config     hasWarned: false,
  vite:config     info: [Function: info],
  vite:config     warn: [Function: warn],
  vite:config     warnOnce: [Function: warnOnce],
  vite:config     error: [Function: error],
  vite:config     clearScreen: [Function: clearScreen],
  vite:config     hasErrorLogged: [Function: hasErrorLogged]
  vite:config   },
  vite:config   packageCache: Map(1) {
  vite:config     'fnpd_/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo' => {
  vite:config       dir: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo',
  vite:config       data: {
  vite:config         name: 'space-coast',
  vite:config         type: 'module',
  vite:config         version: '1.0.1',
  vite:config         scripts: {
  vite:config           dev: 'astro dev',
  vite:config           start: 'astro dev',
  vite:config           build: 'astro build',
  vite:config           preview: 'astro preview',
  vite:config           astro: 'astro',
  vite:config           'config-i18n': 'node ./scripts/config-i18n.js',
  vite:config           'remove-keystatic': 'node ./scripts/remove-keystatic.js'
  vite:config         },
  vite:config         dependencies: {
  vite:config           '@astrojs/mdx': '4.3.4',
  vite:config           '@astrojs/react': '4.3.0',
  vite:config           '@astrojs/rss': '4.0.12',
  vite:config           '@astrojs/sitemap': '3.5.1',
  vite:config           '@fontsource-variable/montserrat': '5.2.5',
  vite:config           '@fontsource-variable/nunito': '5.2.5',
  vite:config           '@glidejs/glide': '^3.7.1',
  vite:config           '@tabler/icons': '3.31.0',
  vite:config           '@tabler/icons-react': '^3.35.0',
  vite:config           '@tailwindcss/forms': '0.5.10',
  vite:config           '@tailwindcss/vite': '4.1.5',
  vite:config           '@types/react': '18.3.20',
  vite:config           '@types/react-dom': '18.3.5',
  vite:config           animejs: '3.2.2',
  vite:config           aos: '^2.3.4',
  vite:config           astro: '5.13.5',
  vite:config           'astro-auto-import': '0.4.4',
  vite:config           'astro-icon': '1.1.5',
  vite:config           'astro-seo': '0.8.4',
  vite:config           'canvas-confetti': '^1.9.3',
  vite:config           'embla-carousel': '^8.6.0',
  vite:config           react: '18.3.1',
  vite:config           'react-dom': '18.3.1',
  vite:config           swiper: '11.2.6',
  vite:config           'tailwind-variants': '1.0.0',
  vite:config           tailwindcss: '4.1.5',
  vite:config           'tw-animate-css': '1.2.9'
  vite:config         },
  vite:config         devDependencies: {
  vite:config           '@eslint/js': '9.25.1',
  vite:config           '@playform/compress': '0.1.9',
  vite:config           'astro-robots-txt': '^1.0.0',
  vite:config           eslint: '9.25.1',
  vite:config           'eslint-plugin-astro': '1.3.1',
  vite:config           'eslint-plugin-jsx-a11y': '6.10.2',
  vite:config           'eslint-plugin-simple-import-sort': '12.1.1',
  vite:config           globals: '16.0.0',
  vite:config           'lodash.debounce': '4.0.8',
  vite:config           'lodash.throttle': '4.1.1',
  vite:config           prettier: '^3.6.2',
  vite:config           'prettier-plugin-astro': '0.14.1',
  vite:config           'prettier-plugin-tailwindcss': '0.6.14',
  vite:config           'typescript-eslint': '8.31.1'
  vite:config         },
  vite:config         pnpm: {
  vite:config           ignoredBuiltDependencies: [ 'esbuild', 'sharp' ],
  vite:config           onlyBuiltDependencies: [ 'esbuild', 'sharp' ]
  vite:config         }
  vite:config       },
  vite:config       hasSideEffects: [Function: hasSideEffects],
  vite:config       setResolvedCache: [Function: setResolvedCache],
  vite:config       getResolvedCache: [Function: getResolvedCache]
  vite:config     },
  vite:config     set: [Function (anonymous)]
  vite:config   },
  vite:config   worker: { format: 'iife', plugins: '() => plugins', rollupOptions: {} },
  vite:config   experimental: { importGlobRestoreExtension: false, hmrPartialAccept: false },
  vite:config   future: undefined,
  vite:config   dev: {
  vite:config     warmup: [],
  vite:config     sourcemap: { js: true },
  vite:config     sourcemapIgnoreList: [Function: isInNodeModules$1],
  vite:config     preTransformRequests: false,
  vite:config     createEnvironment: [Function: defaultCreateDevEnvironment],
  vite:config     recoverable: false,
  vite:config     moduleRunnerTransform: false
  vite:config   },
  vite:config   webSocketToken: '3XIOdbEgpVA_',
  vite:config   getSortedPlugins: [Function: getSortedPlugins],
  vite:config   getSortedPluginHooks: [Function: getSortedPluginHooks],
  vite:config   createResolver: [Function (anonymous)],
  vite:config   fsDenyGlob: [Function: arrayMatcher],
  vite:config   safeModulePaths: Set(0) {},
  vite:config   additionalAllowedHosts: []
  vite:config } +6s
  vite:env loading env files: [
  vite:env   '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/.env',
  vite:env   '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/.env.local',
  vite:env   '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/.env.production',
  vite:env   '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/.env.production.local'
  vite:env ] +16ms
  vite:env env files loaded in 0.33ms +0ms
  vite:env using resolved env: {
  vite:env   SHELL: '/bin/bash',
  vite:env   npm_command: 'run-script',
  vite:env   LSCOLORS: 'Gxfxcxdxdxegedabagacad',
  vite:env   WINDOWID: '94562735289472',
  vite:env   COLORTERM: 'truecolor',
  vite:env   LESS: '-R',
  vite:env   NVM_INC: '/home/loxi1/.nvm/versions/node/v20.18.3/include/node',
  vite:env   XDG_MENU_PREFIX: 'gnome-',
  vite:env   QT_IM_MODULES: 'wayland;ibus',
  vite:env   HISTSIZE: '',
  vite:env   LANGUAGE: '',
  vite:env   NODE: '/home/loxi1/.nvm/versions/node/v20.18.3/bin/node',
  vite:env   VDPAU_DRIVER: 'va_gl',
  vite:env   SSH_AUTH_SOCK: '/run/user/1000/gcr/ssh',
  vite:env   FLYCTL_INSTALL: '/home/loxi1/.fly',
  vite:env   npm_config_verify_deps_before_run: 'false',
  vite:env   SHELL_SESSION_ID: '3bfbafbd3b3c47379bf32f8c25ef4405',
  vite:env   MEMORY_PRESSURE_WRITE: 'c29tZSAyMDAwMDAgMjAwMDAwMAA=',
  vite:env   XMODIFIERS: '@im=ibus',
  vite:env   DESKTOP_SESSION: 'gnome',
  vite:env   OLLAMA_MODELS: '/home/loxi1/.ollama/models',
  vite:env   npm_config_lockfile: 'true',
  vite:env   OSH: '/home/loxi1/.oh-my-bash',
  vite:env   PWD: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo',
  vite:env   LOGNAME: 'loxi1',
  vite:env   XDG_SESSION_DESKTOP: 'gnome',
  vite:env   XDG_SESSION_TYPE: 'wayland',
  vite:env   npm_config_auto_install_peers: 'true',
  vite:env   SYSTEMD_EXEC_PID: '1675',
  vite:env   _: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/.bin/astro',
  vite:env   XAUTHORITY: '/run/user/1000/.mutter-Xwaylandauth.0CU5D3',
  vite:env   GJS_DEBUG_TOPICS: 'JS ERROR;JS LOG',
  vite:env   MOTD_SHOWN: 'pam',
  vite:env   GDM_LANG: 'es_PE.UTF-8',
  vite:env   HOME: '/home/loxi1',
  vite:env   USERNAME: 'loxi1',
  vite:env   LANG: 'es_PE.UTF-8',
  vite:env   LS_COLORS: 'rs=0:di=01;34:ln=01;36:mh=00:pi=40;33:so=01;35:do=01;35:bd=40;33;01:cd=40;33;01:or=40;31;01:mi=00:su=37;41:sg=30;43:ca=00:tw=30;42:ow=34;42:st=37;44:ex=01;32:*.7z=01;31:*.ace=01;31:*.alz=01;31:*.apk=01;31:*.arc=01;31:*.arj=01;31:*.bz=01;31:*.bz2=01;31:*.cab=01;31:*.cpio=01;31:*.crate=01;31:*.deb=01;31:*.drpm=01;31:*.dwm=01;31:*.dz=01;31:*.ear=01;31:*.egg=01;31:*.esd=01;31:*.gz=01;31:*.jar=01;31:*.lha=01;31:*.lrz=01;31:*.lz=01;31:*.lz4=01;31:*.lzh=01;31:*.lzma=01;31:*.lzo=01;31:*.pyz=01;31:*.rar=01;31:*.rpm=01;31:*.rz=01;31:*.sar=01;31:*.swm=01;31:*.t7z=01;31:*.tar=01;31:*.taz=01;31:*.tbz=01;31:*.tbz2=01;31:*.tgz=01;31:*.tlz=01;31:*.txz=01;31:*.tz=01;31:*.tzo=01;31:*.tzst=01;31:*.udeb=01;31:*.war=01;31:*.whl=01;31:*.wim=01;31:*.xz=01;31:*.z=01;31:*.zip=01;31:*.zoo=01;31:*.zst=01;31:*.avif=01;35:*.jpg=01;35:*.jpeg=01;35:*.jxl=01;35:*.mjpg=01;35:*.mjpeg=01;35:*.gif=01;35:*.bmp=01;35:*.pbm=01;35:*.pgm=01;35:*.ppm=01;35:*.tga=01;35:*.xbm=01;35:*.xpm=01;35:*.tif=01;35:*.tiff=01;35:*.png=01;35:*.svg=01;35:*.svgz=01;35:*.mng=01;35:*.pcx=01;35:*.mov=01;35:*.mpg=01;35:*.mpeg=01;35:*.m2v=01;35:*.mkv=01;35:*.webm=01;35:*.webp=01;35:*.ogm=01;35:*.mp4=01;35:*.m4v=01;35:*.mp4v=01;35:*.vob=01;35:*.qt=01;35:*.nuv=01;35:*.wmv=01;35:*.asf=01;35:*.rm=01;35:*.rmvb=01;35:*.flc=01;35:*.avi=01;35:*.fli=01;35:*.flv=01;35:*.gl=01;35:*.dl=01;35:*.xcf=01;35:*.xwd=01;35:*.yuv=01;35:*.cgm=01;35:*.emf=01;35:*.ogv=01;35:*.ogx=01;35:*.aac=00;36:*.au=00;36:*.flac=00;36:*.m4a=00;36:*.mid=00;36:*.midi=00;36:*.mka=00;36:*.mp3=00;36:*.mpc=00;36:*.ogg=00;36:*.ra=00;36:*.wav=00;36:*.oga=00;36:*.opus=00;36:*.spx=00;36:*.xspf=00;36:*~=00;90:*#=00;90:*.bak=00;90:*.crdownload=00;90:*.dpkg-dist=00;90:*.dpkg-new=00;90:*.dpkg-old=00;90:*.dpkg-tmp=00;90:*.old=00;90:*.orig=00;90:*.part=00;90:*.rej=00;90:*.rpmnew=00;90:*.rpmorig=00;90:*.rpmsave=00;90:*.swp=00;90:*.tmp=00;90:*.ucf-dist=00;90:*.ucf-new=00;90:*.ucf-old=00;90:',
  vite:env   XDG_CURRENT_DESKTOP: 'GNOME',
  vite:env   KONSOLE_DBUS_SERVICE: ':1.126',
  vite:env   npm_package_version: '1.0.1',
  vite:env   MEMORY_PRESSURE_WATCH: '/sys/fs/cgroup/user.slice/user-1000.slice/user@1000.service/session.slice/org.gnome.Shell@wayland.service/memory.pressure',
  vite:env   WAYLAND_DISPLAY: 'wayland-0',
  vite:env   KONSOLE_DBUS_SESSION: '/Sessions/1',
  vite:env   PROFILEHOME: '',
  vite:env   OLLAMA_GPU_LAYERS: '10',
  vite:env   INVOCATION_ID: '9d0723e79e33416487a4c5c5976fa6fa',
  vite:env   KONSOLE_VERSION: '250801',
  vite:env   MANAGERPID: '885',
  vite:env   INIT_CWD: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo',
  vite:env   npm_lifecycle_script: 'astro build --verbose',
  vite:env   GJS_DEBUG_OUTPUT: 'stderr',
  vite:env   NVM_DIR: '/home/loxi1/.nvm',
  vite:env   GNOME_SETUP_DISPLAY: ':1',
  vite:env   npm_config_force: '',
  vite:env   XDG_SESSION_CLASS: 'user',
  vite:env   TERM: 'xterm-256color',
  vite:env   npm_package_name: 'space-coast',
  vite:env   USER: 'loxi1',
  vite:env   npm_config_frozen_lockfile: '',
  vite:env   COLORFGBG: '15;0',
  vite:env   npm_config_allowed_builds: 'esbuild sharp',
  vite:env   DISPLAY: ':0',
  vite:env   npm_lifecycle_event: 'build',
  vite:env   SHLVL: '1',
  vite:env   NVM_CD_FLAGS: '',
  vite:env   PAGER: 'less',
  vite:env   QT_IM_MODULE: 'ibus',
  vite:env   MANAGERPIDFDID: '886',
  vite:env   npm_config_user_agent: 'pnpm/10.6.2 npm/? node/v20.18.3 linux x64',
  vite:env   PNPM_SCRIPT_SRC_DIR: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo',
  vite:env   npm_execpath: '/home/loxi1/.nvm/versions/node/v20.18.3/lib/node_modules/pnpm/bin/pnpm.cjs',
  vite:env   LC_CTYPE: 'es_PE.UTF-8',
  vite:env   XDG_RUNTIME_DIR: '/run/user/1000',
  vite:env   DEBUGINFOD_URLS: 'https://debuginfod.archlinux.org ',
  vite:env   JOURNAL_STREAM: '9:18543',
  vite:env   XDG_DATA_DIRS: '/home/loxi1/.local/share/flatpak/exports/share:/var/lib/flatpak/exports/share:/usr/local/share/:/usr/share/',
  vite:env   npm_config_node_linker: 'hoisted',
  vite:env   PATH: '/home/loxi1/Documentos/Proyectos/Astro/NeoMaxilo/NeoMaxilo/node_modules/.bin:/home/loxi1/.nvm/versions/node/v20.18.3/lib/node_modules/pnpm/dist/node-gyp-bin:/home/loxi1/.nvm/versions/node/v20.18.3/bin:/home/loxi1/.fly/bin:/usr/local/bin:/usr/bin:/usr/local/sbin:/var/lib/flatpak/exports/bin:/usr/lib/jvm/default/bin:/usr/bin/site_perl:/usr/bin/vendor_perl:/usr/bin/core_perl:/home/loxi1/.config/composer/vendor/bin:/home/loxi1/.local/bin',
  vite:env   npm_config_node_gyp: '/home/loxi1/.nvm/versions/node/v20.18.3/lib/node_modules/pnpm/dist/node_modules/node-gyp/bin/node-gyp.js',
  vite:env   HISTIGNORE: '&:[ ]*:exit:ls:bg:fg:history:clear',
  vite:env   GDMSESSION: 'gnome',
  vite:env   HISTFILESIZE: '',
  vite:env   OLLAMA_KEEP_ALIVE: '0',
  vite:env   DBUS_SESSION_BUS_ADDRESS: 'unix:path=/run/user/1000/bus',
  vite:env   MAIL: '/var/spool/mail/loxi1',
  vite:env   NVM_BIN: '/home/loxi1/.nvm/versions/node/v20.18.3/bin',
  vite:env   npm_config_registry: 'https://registry.npmjs.org/',
  vite:env   GIO_LAUNCHED_DESKTOP_FILE_PID: '7224',
  vite:env   npm_node_execpath: '/home/loxi1/.nvm/versions/node/v20.18.3/bin/node',
  vite:env   GIO_LAUNCHED_DESKTOP_FILE: '/usr/share/applications/org.kde.konsole.desktop',
  vite:env   OLDPWD: '/home/loxi1',
  vite:env   OLLAMA_NO_CUDA: '0',
  vite:env   KONSOLE_DBUS_WINDOW: '/Windows/1',
  vite:env   DEBUG: 'astro:*,vite:*',
  vite:env   NODE_ENV: 'production'
  vite:env } +1ms
11:24:56 [vite] ✓ 30 modules transformed.
11:24:57 [vite] dist/_astro/HeroCarruselSwitch.astro_astro_type_script_index_0_lang.D9ylC1o1.js      0.34 kB │ gzip:  0.24 kB
11:24:57 [vite] dist/_astro/thanks.astro_astro_type_script_index_0_lang.CEFrGR-Q.js                  0.40 kB │ gzip:  0.22 kB
11:24:57 [vite] dist/_astro/gracias.astro_astro_type_script_index_0_lang.CEFrGR-Q.js                 0.40 kB │ gzip:  0.22 kB
11:24:57 [vite] dist/_astro/MobileLanguageSelect.astro_astro_type_script_index_0_lang.ChqrdJBe.js    0.73 kB │ gzip:  0.36 kB
11:24:57 [vite] dist/_astro/Nav.astro_astro_type_script_index_0_lang.DlnyfcX1.js                     1.02 kB │ gzip:  0.46 kB
11:24:57 [vite] dist/_astro/MobileNavDropdown.astro_astro_type_script_index_0_lang.DpljasSJ.js       1.17 kB │ gzip:  0.45 kB
11:24:57 [vite] dist/_astro/HeroCarruselDesktop.astro_astro_type_script_index_0_lang.Bbtm5-8u.js     1.29 kB │ gzip:  0.67 kB
11:24:57 [vite] dist/_astro/MobileNav.astro_astro_type_script_index_0_lang.DGfN1M56.js               1.35 kB │ gzip:  0.47 kB
11:24:57 [vite] dist/_astro/HeroCarruselMobile.astro_astro_type_script_index_0_lang.B-M3eUs4.js      1.62 kB │ gzip:  0.81 kB
11:24:57 [vite] dist/_astro/Accordion.astro_astro_type_script_index_0_lang.CoyTbeo2.js               2.55 kB │ gzip:  0.99 kB
11:24:57 [vite] dist/_astro/Dropdown.astro_astro_type_script_index_0_lang.ZQ5tvlml.js                5.41 kB │ gzip:  1.58 kB
11:24:57 [vite] dist/_astro/client.DtF5yZE9.js                                                     143.47 kB │ gzip: 46.21 kB
11:24:57 [vite] ✓ built in 930ms

 generating static routes 
 loxi1@acme  ~/.../NeoMaxil
