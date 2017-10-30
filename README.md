# atomizer
Atom/RSS web reader

## Capabilities
- Atom 1.0 (rfc5023)
- RSS 2.0

## Deploy
Before deploy, you can change some configurations editing ```docker-compose.yml```
 - Database configuration
 - DNS Domains

Use **docker-compose** to build the image and deploy the web and database servers:
```
git clone https://github.com/reimashi/atomizer.git atomizer
cd atomizer

docker-compose -p atomizer -f docker/devel/docker-compose.yml up
[OR]
docker-compose -p atomizer -f docker/production/docker-compose.yml up
```

After deployment, you should view 4 docker images running:
 - **client:** An nginx static server that serve the frontend
 - **api:** An apache server serving the backend api in cakephp
 - **database:** An mariadb server as api database
 - **proxy:** An nginx proxy that connects everything