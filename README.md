# Web

Website of [Zürcher Studierendenzeitung](https://www.zsonline.ch), the
independent student newspaper for the University of Zurich and ETH Zurich.

## Installation

### Development

It is recommended to use [DDEV](https://ddev.readthedocs.io/en/stable/) for
local development. These instructions assume that you follow our recommendation.

Once [DDEV](https://ddev.readthedocs.io/en/stable/) is installed, open a
terminal in the project directory and start all containers.

```
ddev start
```

Next, install all PHP dependencies.

```
ddev composer update
```

Then, create a `.env` file. You can copy the example file, which contains all
required variables.

```
cp .env.example.dev .env
```

Provision the database and create the administrator account. When asked to
enter the _Site name_, _Site URL_, and _Site language_, keep the default
values.

```
ddev craft install
```

So far, you have installed an empty Craft CMS project. You can visit the control
panel at `https://zs.ddev.site/admin/`.

In order to load our own project configuration, ensure that Craft CMS has not
overwritten any configuration files. If it has, revert the changes. Then, apply
our project configuration.

```
ddev craft project-config/apply
```

### Production

When you are ready to deploy, clone the repository to your production host. It
is assumed that it runs Apache (or LiteSpeed) server.

Next, install all dependencies.

```
php craft install
```

Create a `.env` file. You can use the example file as a starting point. The
licensing information can be found in the
[Craft Console](https://console.craftcms.com/).

```
cp .env.example.production .env
```

Lastly, import a database snapshot and manually copy all assets into their
volume directory (e.g. `./web/assets/images/`).

```
php craft db/restore ./snapshot.zip
```

## Development

The website is built around [Craft CMS](https://craftcms.com/). Please consult
the [documentation](https://craftcms.com/docs/) to learn about how this
repository is structured and how the CMS works.

To simplify development, we have included a development server. To make use of
it, install the Node.js dependencies in your development environment.

```
ddev npm install
```

Then, start the development server, which provides hot module replacement.

```
ddev npm run dev
```

Once you are finished developing, you need to build a bundle, which can be
served in production.

```
ddev npm run build
```

## Update

Develop everything locally. This also includes updating dependencies or changing
settings. When everything is ready and has been pushed, pull the changes onto
the production host.

```
git pull
```

Next, apply any new database migrations and the project config changes.

```
php craft migrate/all
php craft project-config/apply
```

Finally, refresh the cache.

```
php craft blitz/cache/refresh
```
