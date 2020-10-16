PHP_VERSION ?= 7.4
DRUSH ?= 10
REPOSITORY = docker.pkg.github.com/city-of-helsinki/drupal-helfi/drupal-php-docker
BASE_IMAGE_TAG = $(PHP_VERSION)

ifeq ($(TAG),)
	TAG = $(PHP_VERSION)
endif

.PHONY: build push release

default: build

build:
	docker build -t $(REPOSITORY):$(TAG) --build-arg DRUSH=$(DRUSH) --build-arg BASE_IMAGE_TAG=$(TAG) ./

push:
	docker push $(REPOSITORY):$(TAG)

release: build push
