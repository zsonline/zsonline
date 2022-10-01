# Editor

A purpose-built [Tiptap](https://tiptap.dev)-based block editor field for
[Craft CMS](https://craftcms.com/), which stores content in structured JSON.

## Installation

The editor is already installed as a Craft CMS module. No additional steps are
required.

## Development

It is recommended to use DDEV for local development. We assume that you have
already started the development environment as explained in the repository's
[README](../../README.md).

Once DDEV is installed and running, open a terminal in the editor module
directory and install all Node dependencies.

```
ddev npm install
```

Next, you can start the [Vite](https://vitejs.dev/) development server, which
provides hot module replacement.

```
ddev npm run dev
```

Finally, you need to set `VITE_PLUGIN_DEVSERVER=1` in your local `.env` file.
Otherwise, the development server's assets will not be injected into the
website. This is a setting of
[craft-plugin-vite's](https://github.com/nystudio107/craft-plugin-vite), which
we use for combining Craft CMS with Vite and DDEV.

### Building

Once you are finished developing the editor, you need to build a bundle, which
can be used in production.

```
ddev npm run build
```

When the Vite development server is not running, the bundle is served instead.
Make sure to set `VITE_PLUGIN_DEVSERVER=0` or remove it altogether from your
`.env` in your production environment.
