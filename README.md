## Santex Assignment

API project to expose endpoints for:

- Importing football league data from third party API
- Counting players (from database) involved in a requested league 

#### Optimizations & Future tasks to tackle
  
  - Better error handling
  - Data validation
  - Unit tests

## Installation

####Build the docker image
Open a terminal and run `docker-compose up -d --build`.

####Generate Laravel key
Run `docker-compose run --rm artisan key:generate`

####Create database
Run `docker-compose run --rm artisan migrate`

##Usage

Open up your browser of choice to [http://localhost:8080](http://localhost:8080) and you should see your app running as intended.

####Endpoints

- GET http://127.0.0.1:8080/import-league/{league-code}
- GET http://127.0.0.1:8080/total-players/{league-code}

####Useful commands:

- `docker-compose run --rm composer update`
- `docker-compose run --rm artisan migrate`

####Containers created and their ports are as follows:

- **nginx** - `:8080`
- **mysql** - `:3306`
- **php** - `:9000`
