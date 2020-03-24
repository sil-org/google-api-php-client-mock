it-now: clean install phpunit

clean:
	sudo rm -rf ./vendor
	rm -f composer.lock

install:
	docker-compose run --rm cli bash -c "cd /data;composer install"

update:
	docker-compose run --rm cli bash -c "cd /data;composer update"

phpunit:
	docker-compose run --rm cli bash -c "cd /data/SilMock/tests;./phpunit"

# For use in virtualbox.
test:
	cd /var/lib/GA_mock/SilMock/tests; /var/lib/GA_mock/SilMock/tests/phpunit
