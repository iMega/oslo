COMPOSER_FLAGS = --ignore-platform-reqs --no-interaction --no-dev -o
TELEPORT_FILEMAN ?= imegateleport/tokio
TELEPORT_PARSER ?= imegateleport/oslo

build: build-fs
	@docker build -t imegateleport/oslo .

push:
	@docker push imegateleport/oslo:latest

build_dir:
	@-mkdir -p $(CURDIR)/build

build-fs: build_dir
	@docker run --rm \
		-v $(CURDIR)/runner:/runner \
		-v $(CURDIR)/build:/build \
		-v $(CURDIR)/src:/src \
		-v $(CURDIR)/vendor:/src/vendor \
		-v $(CURDIR)/config:/src/config \
		imega/base-builder:1.1.1 \
		--packages="php php-json rsync inotify-tools"

get_containers:
	$(eval CONTAINERS := $(subst build/containers/,,$(shell find build/containers -type f)))

stop: get_containers
	@-docker stop $(CONTAINERS)

clean: stop
	@-docker rm -fv $(CONTAINERS)
	@rm -rf build/containers/*

build/composer:
	@mkdir -p $(shell dirname $@)
	@docker run --rm -v $(CURDIR):/data imega/composer update $(COMPOSER_FLAGS)
	@touch $@

build/containers/teleport_fileman:
	@mkdir -p $(shell dirname $@)
	@docker run -d \
		--name teleport_fileman \
		--restart=always \
		-v $(CURDIR)/data:/data \
		$(TELEPORT_FILEMAN)
	@touch $@

build/containers/teleport_tester:
	@cd tests;docker build -t imegateleport/oslo_tester .

build/containers/teleport_parser:
	@mkdir -p $(shell dirname $@)
	@docker run -d --name teleport_parser --restart=always \
		--link teleport_fileman:fileman \
		$(TELEPORT_PARSER)
	@touch $@

discovery_parser:
	while [ "`docker inspect -f {{.State.Running}} teleport_parser`" != "true" ]; do \
		echo "wait teleport_parser"; sleep 0.3; \
	done
	$(eval IP := $(shell docker inspect --format '{{ .NetworkSettings.IPAddress }}' teleport_parser))
	docker exec teleport_fileman sh -c 'echo -e "$(IP)\tparser" >> /etc/hosts'

data_dir:
	@-mkdir -p $(CURDIR)/data/zip $(CURDIR)/data/unzip $(CURDIR)/data/storage

test: data_dir build/containers/teleport_fileman build/containers/teleport_parser discovery_parser build/containers/teleport_tester
	@docker run --rm \
		--link teleport_parser:parser \
		-v $(CURDIR)/tests/fixtures:/data/unzip \
		imegateleport/oslo_tester \
		rsync --inplace -av /data/unzip/9915e49a-4de1-41aa-9d7d-c9a687ec048d rsync://parser/data
	@if [ ! -f "$(CURDIR)/data/storage/9915e49a-4de1-41aa-9d7d-c9a687ec048d/dump.sql" ];then \
		exit 1; \
	fi
