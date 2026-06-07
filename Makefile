php ?= 8.1

help:
	@echo "Available options:"
	@echo "    make up"
	@echo "    make up php=$(env)"
	@echo "    make test"
	@echo "    make coverage"

up:
	docker rm -f sonos > /dev/null 2>&1
	docker build . -t sonos-image --build-arg PHP_VERSION=$(php) --build-arg COVERAGE=pcov
	docker run --interactive --detach --volume $(shell pwd):/app --network host --name sonos sonos-image
	docker exec -ti sonos composer update

test:
	docker exec -ti sonos composer test

coverage:
	docker exec -ti sonos vendor/bin/phpunit --coverage-html=dev/coverage
	sudo chown -R $$USER dev/coverage/
	firefox dev/coverage/index.html
