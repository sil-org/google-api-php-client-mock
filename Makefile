it-now: clean install phpunit

clean:
	sudo rm -rf ./vendor
	rm -f composer.lock

install:
	docker-compose run --rm cli bash -c "cd /vagrant;composer install"

update:
	docker-compose run --rm cli bash -c "cd /vagrant;composer update"

phpunit:
	docker-compose run --rm cli bash -c "cd /vagrant/SilMock/tests;./phpunit"

# For use in virtualbox.
test:
	cd /var/lib/GA_mock/SilMock/tests; /var/lib/GA_mock/SilMock/tests/phpunit
