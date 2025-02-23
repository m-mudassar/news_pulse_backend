
## Steps to run this project

For ease of use i am also commiting .env file along with api keys.
Following are the steps to run this project
1. PHP 8.2 or higher
2. cd into project and run `composer update`
3. Create a database and update the .env file with the database credentials
4. Run `php artisan migrate` to create the tables
5. Run `php artisan db:seed` to seed the tables
6. Run `php artisan serve` to start the server
7. Server will start listening on http://127.0.0.1:8000/
