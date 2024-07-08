# Nuxt 3 + Laravel Authentication

Discover the perfect solution for user authentication with the Nuxt 3 and Laravel boilerplate. Combining the strengths of both frameworks, it provides a solid and secure foundation for any web project.

## Setup API (Laravel)

Switch to the api folder

    cd api

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

Start the local development server

    php artisan serve

The api can be accessed at [http://localhost:8000/api](http://localhost:8000/api).

## Setup Frontend (Nuxt 3)

Switch to the frontend folder

    cd frontend 

Install all the dependencies

    npm install

Start the local development server

    npm run dev

The frontend can be accessed at [http://localhost:3000](http://localhost:3000).
