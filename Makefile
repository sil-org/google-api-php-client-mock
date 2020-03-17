test:
	cd /var/lib/GA_mock/SilMock/tests; /var/lib/GA_mock/SilMock/tests/phpunit

phpunit:
	docker-compose run cli bash -c "cd /data/SilMock/tests;./phpunit"
