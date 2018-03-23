SRC_DIRECTORY = src
TEST_DIRECTORY = tests

test: test-unit
lint: lint-md lint-cs

# Run every available unit test
test-unit:
	vendor/bin/phpunit --bootstrap vendor/autoload.php $(TEST_DIRECTORY)

# Run PHP Mess Detector against all the code, settings are in phpmd.xml
lint-md:
	vendor/bin/phpmd $(SRC_DIRECTORY) text ./phpmd.xml

# Run PHP Code Sniffer against all the code, settings are in phpcs.xml
lint-cs:
	vendor/bin/phpcs $(SRC_DIRECTORY) -s
