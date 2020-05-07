# Project 7


# How to install the project :

  - Download the project
  - Create the database with the command : php bin/console doctrine:database:create
  - Update all the entries in the database with the command : php bin/console doctrine:schema:update --force
  - Run the application with the command : php bin/console server:run
  - Create a client with the command : php bin/console fos:user:create "name here"
  - Generate cliend_id and client_secret with the command : php bin/console fos:oauth-server:create-client --redirect-uri="http://127.0.0.1:8000" --grant-type="password"
  - Use the generated credentials to access the api

