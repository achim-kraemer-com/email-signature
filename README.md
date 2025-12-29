# Signature Generator

This is a web application to create email signatures.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

- Docker
- Docker Compose

### Installation

1.  Clone the repo
    ```sh
    git clone https://github.com/your_username_/Project-Name.git
    ```
2.  Navigate to the project directory
    ```sh
    cd signature
    ```
3.  Create a `.env` file from the `.env.dist` file in the `docker` directory and fill in the required environment variables.
    ```sh
    cp docker/.env.dist docker/.env
    ```
4.  Build and run the Docker containers
    ```sh
    docker-compose up -d
    ```
5.  Install dependencies
    ```sh
    docker-compose exec php composer install
    ```

## Usage

The application should now be running at [http://localhost:8080](http://localhost:8080).

## Built With

*   [Symfony](https://symfony.com/) - The PHP framework used
*   [Doctrine](https://www.doctrine-project.org/) - ORM
*   [Twig](https://twig.symfony.com/) - Template Engine
*   [Docker](https://www.docker.com/) - Containerization

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.