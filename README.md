# atomizer
Atom/RSS web reader

## Capabilities
- Atom 1.0 (rfc5023)
- RSS 2.0

## Known bugs
- JWT token read on server fails. A bug of [CakePHP lib](https://github.com/ADmad/cakephp-jwt-auth)? Meanwhile, all loguedin users is identified as test user. Login and create new user methods works.

## Deploy
Before deploy, you can change some configurations editing ```docker/[type]/docker-compose.yml```
 - Database configuration
 - DNS Domains

First, clone the repo
```
git clone https://github.com/reimashi/atomizer.git atomizer
cd atomizer
```

To install server dependencies
```
cd src/server
compose require
```

To install client dependencies and generate bin files
```
cd src/client
npm install
webpack
```

At last, deploy the dockers with docker-compose
 - type: Type of deploy. devel, production...
```
docker-compose -p atomizer -f docker/[type]/docker-compose.yml up
```

After deployment, you should have 4 docker images running:
 - **client:** A nginx static server that serve the frontend
 - **api:** An apache server serving the backend api in cakephp
 - **database:** A mariadb server as api database
 - **proxy:** A nginx proxy that connects everything