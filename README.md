# City of Helsinki - Drupal 9

City of Helsinki

## Includes

- Drupal 9.0.x
- Drush 10.x
- Docker setup for development, see [docker-compose.yml](docker-compose.yml)
- [druidfi/tools](https://github.com/druidfi/tools)
- Web root is `/public`
- Configuration is in `/conf/cmi`
- Custom modules are created in `/public/modules/custom`

## Environments

Env | Branch | Drush alias | URL
--- | ------ | ----------- | ---
development | * | - | http://helfi.docker.sh/
production | master | @main | TBD

## Requirements

You need to have these applications installed to operate on local environment:

- [Docker](https://github.com/druidfi/guidelines/blob/master/docs/docker.md)
- [Stonehenge](https://github.com/druidfi/stonehenge)

## Create and start the environment

For the first time (new project):

```
$ make new
```

And following times to create and start the environment:

```
$ make fresh
```

NOTE: Change these according of the state of your project.

## Login to Drupal container

This will log you inside the app container:

```
$ make shell
```

## Updating testing images

See [docker/ci/README.md](docker/ci/README.md) for more information.
