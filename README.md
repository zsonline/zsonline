# [zsonline.ch](https://www.zsonline.ch/)

Website of Zürcher Studierendenzeitung, the independent student newspaper for the University of Zurich and ETH Zurich.

1. [Installing Locally](#installing-locally)
2. [Developing Locally](#developing-locally)
3. [Setting Up Production](#setting-up-production)
4. [Updating Production](#updating-production)

## Installing Locally

We recommend using [DDEV](https://ddev.readthedocs.io/en/stable/) for local development.

1. Install DDEV and open a terminal in the project directory. Start all containers by running:

   ```bash
   ddev start
   ```

2. Install all PHP dependencies with the following command:

   ```bash
   ddev composer install
   ```

3. Create a `.env` file by copying the example file:

   ```bash
   cp .env.example.dev .env
   ```

4. Install Craft CMS. When asked to enter the site name, site URL, and site language, keep the default values:

   ```bash
   ddev craft install
   ```

   Once installed, you can visit the control panel at [https://zs.ddev.site/admin/](https://zs.ddev.site/admin/).

5. Check that Craft CMS has not overwritten any configuration files. If it has, revert the changes. Then apply the project configuration:

   ```bash
   ddev craft project-config/apply
   ```

## Developing Locally

The website is built with [Craft CMS](https://craftcms.com/). You can consult the [Craft documentation](https://craftcms.com/docs/) to learn more about how the CMS works and how the repository is structured.

1. Install the Node.js dependencies in your development environment:

   ```bash
   ddev npm install
   ```

2. Start the development server, which includes hot module replacement, by running:

   ```bash
   ddev npm run dev
   ```

3. When you are finished developing, build the assets so they can be used in production:

   ```bash
   ddev npm run build
   ```

## Setting Up Production

1. Clone the repository to your production server. The instructions assume the server runs Apache or LiteSpeed.

2. Install all PHP dependencies by running:

   ```bash
   php craft install
   ```

3. Create a `.env` file using the example file as a template:

   ```bash
   cp .env.example.production .env
   ```

   Add the license information from the [Craft Console](https://console.craftcms.com/).

4. Import a database snapshot and manually copy all assets into their respective directories (for example, `./web/assets/images/`):

   ```bash
   php craft db/restore ./snapshot.sql
   ```

## Updating Production

1. Pull the latest changes from the repository:

   ```bash
   git pull
   ```

2. Install any new PHP dependencies:

   ```bash
   composer install
   ```

3. Apply new database migrations and project configuration updates:

   ```bash
   php craft migrate/all
   php craft project-config/apply
   ```
