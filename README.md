# HackDay18 BrewMe

## Setup
- Copy `docker-compose.example.yml` to `docker-compose.yml` and set required environment variables
- Copy `.env.example` to `.env` and set required environment variables
- `docker-compose exec php vendor/bin/phinx init`
- Amend `phinx.yml` details for mysql connection
- Login to the database `docker-compose exec db mysql- -uroot -p` and `CREATE DATABASE brewme CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- `docker-compose exec php vendor/bin/phinx migrate`


## DB
### Create migration
`docker-compose exec php vendor/bin/phinx create YourTable`