it-now: clean composerupdate phpunit

clean:
	sudo rm -rf ./vendor
	rm -f composer.phar
	rm -f composer.lock

composerupdate:
	docker-compose run cli bash -c "cd /data/; ./composer-update.sh"

phpunit:
	docker-compose run cli bash -c "cd /data/SilMock/tests;./phpunit"

test:
	cd /var/lib/GA_mock/SilMock/tests; /var/lib/GA_mock/SilMock/tests/phpunit
