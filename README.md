# Query play studio. In progress now! 🏗️

## About 'Query play studio'

This is a query play studio, written in and for educational and demonstrational purposes.

A full-stack SPA platform for mastering database query writing across various database systems. Provides secure sandbox environments with different databases for hands-on experimentation. Supports both free exploration and structured exercise modes. Features an AI mentor using cloud or local models for intelligent query analysis, performance optimization, and guidance on advanced query language constructs.

Based on tech stack:
- [HTML](https://developer.mozilla.org/en-US/docs/Web/HTML),
- [PHP](https://www.php.net),
- [Symfony](https://symfony.com),
- [MySQL](https://www.mysql.com),
- [TypeScript](https://www.typescriptlang.org),
- [Vue](https://vuejs.org),
- [Vue Router](https://router.vuejs.org/),
- [Pinia](https://pinia.vuejs.org),
- [Axios](https://axios-http.com),
- [CSS](https://developer.mozilla.org/en-US/docs/Web/CSS),
- [TailwindCss](https://tailwindcss.com),
- [Docker](https://www.docker.com).

## Getting Started

- Clone the repository:
``` bash
git clone [repository-url]
```

- Change directory to project:
``` bash
cd /path/to/query-play-studio/
```

- Add file .env.local with your parameters

- Run Docker Desktop (with wsl - for Windows only)

- Run wsl (for Windows only):
``` bash
wsl
```

- Up docker compose:
``` bash
docker-compose --env-file .env.local up -d
```

or

``` bash
./docker/run.sh
```

- Also available other scripts:
``` bash
./docker/build.sh
./docker/stop.sh
./docker/kill.sh
```

- Generate application key:
``` bash
docker compose exec php bin/console secrets:generate-keys
docker compose exec php bin/console secrets:set APP_SECRET
```

or

``` bash
docker compose exec php php -r "echo 'APP_SECRET=' . bin2hex(random_bytes(32)) . PHP_EOL;"
```

- Add new tab in terminal and connect to container:
``` bash
docker exec -it qps-php bash
```

- Install php dependencies:
``` bash
composer install
```

- Install node dependencies:
``` bash
npm install
```

- Build project:
``` bash
npm run build
```

- In browser go to http://localhost/

## Development and Production Modes

### Development Mode (Hot Module Replacement)
- Start the Vite dev server inside the Docker container: `npm run dev`
- Note: The frontend is located in the `frontend` directory, so the command runs from `/app/frontend` in the container.
- Vite will run on port 3000 and provide hot reloading for Vue components.
- Symfony with Caddy runs on port 80 and serves the HTML page.
- The Twig template will load scripts from `http://localhost:3000/` for HMR.
- Open your browser at `http://localhost` to see the application.

### Production Mode (Compiled Assets)
- Build the frontend assets inside the Docker container: `npm run build`
- Note: The frontend is located in the `frontend` directory, so the command runs from `/app/frontend` in the container.
- This will compile and bundle all assets into the `public/build/` directory (in the project root, one level up from `frontend`).
- The main files are `app.js` (JavaScript bundle), `app.css` (all CSS styles in one file), and chunk files in the `chunks/` folder.
- Set the Symfony environment to production by setting `APP_ENV=prod` and `APP_DEBUG=0` in your `.env.local` file.
- Clear the Symfony cache for the production environment: `php bin/console cache:clear --env=prod`
- Restart the Docker container (or ensure Caddy is serving the updated `public/build/` directory) and open `http://localhost`.

### Switching Between Modes
- To switch from development to production: Stop the Vite dev server (Ctrl+C), run the build command, set `APP_ENV=prod`, and clear the cache.
- To switch from production to development: Delete the `public/build/` folder, set `APP_ENV=dev`, and start the Vite dev server again.

### Asset Locations
- In development, all scripts and styles are served by the Vite dev server from `http://localhost:3000/`.
- In production, all assets are static files in the `public/build/` directory (located at the project root) and served by Caddy.

### Environment Variables
- `APP_ENV` (in `.env.local`): Controls which scripts are included by Twig (`dev` for Vite HMR, `prod` for compiled assets).
- `NODE_ENV`: Automatically set by Vite (`development` when running `npm run dev`, `production` when building).

That's it! Thank you!

## Screenshots

<img width="1920" height="1200" alt="2025-12-12_09-13-17" src="https://github.com/user-attachments/assets/69aace3e-57b1-4023-9750-01ba097faaeb" />
<img width="1920" height="1200" alt="2025-12-12_09-14-47" src="https://github.com/user-attachments/assets/aa57be83-7d78-4733-a366-15aa75406f45" />

## License

The 'Query play studio' is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
