server := "levynkeneng.dev@212.47.70.2"
subdomain := "symfonyblog.levynkeneng.dev"

.PHONY: install deploy

deploy:
	ssh -A $(server) 'cd domains/$(subdomain)/public_html && git fetch && git pull && make install'

install: vendor/autoload.php
	composer dump-env prod
	npm install
	npm run build
	php bin/console cache:clear
	php bin/console doctrine:migrations:migrate -n

vendor/autoload.php: composer.lock composer.json
	composer install --optimize-autoloader
	touch vendor/autoload.php