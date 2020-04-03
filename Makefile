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

# For use in virtualbox.
test:
	cd /var/lib/GA_mock/SilMock/tests; /var/lib/GA_mock/SilMock/tests/phpunit
