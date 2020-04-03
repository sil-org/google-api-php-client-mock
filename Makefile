it-now: clean install phpunit

clean:
	docker-compose kill
	docker system prune -f

install:
	docker-compose run --rm cli bash -c "composer install"

update:
	docker-compose run --rm cli bash -c "composer update"

phpunit:
	docker-compose run --rm cli bash -c "cd /data/SilMock/tests; ./phpunit"

test: install phpunit
