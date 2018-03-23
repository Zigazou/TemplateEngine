PHPMD_RULESETS = "cleancode,codesize,controversial,design,naming,unusedcode"
SRC_DIRECTORY = src
TEST_DIRECTORY = tests

test: test-unit test-md

test-unit:
	vendor/bin/phpunit --bootstrap vendor/autoload.php $(TEST_DIRECTORY)

test-md:
	vendor/bin/phpmd $(SRC_DIRECTORY) text $(PHPMD_RULESETS)

