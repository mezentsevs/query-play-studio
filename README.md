# Query Play Studio. In progress now!

## About 'Query Play Studio'

This is a query play studio, written in and for educational and demonstrational purposes.

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
- [Docker](https://www.docker.com),
- [Swagger](https://swagger.io),
- [Postman](https://www.postman.com).

## Getting Started

- Clone the repository:
``` bash
git clone [repository-url]
```

- Change directory to project:
``` bash
cd /path/to/query-play-studio/
```

- Add file .env.local with your parameters:
``` bash
MYSQL_ROOT_PASSWORD=
MYSQL_DATABASE=
MYSQL_USER=
MYSQL_PASSWORD=
DATABASE_URL=
```

- Run Docker Desktop (with wsl - for Windows only)

- Run wsl (for Windows only):
``` bash
wsl
```

- Build docker compose:
``` bash
docker compose build
```

- Up docker compose:
``` bash
docker compose up -d
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
docker exec -it query-play-studio-php-1 bash
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

That's it! Thank you!

## Screenshots

## License

The 'Query Play Studio' is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
