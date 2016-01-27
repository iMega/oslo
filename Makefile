build:
	docker run --rm -v $(CURDIR):/data imega/composer:1.2.0 update
