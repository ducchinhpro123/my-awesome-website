## Run

1. Setup .env file

2. Install dependencies

```bash
composer dump-autoload

composer install
```

3. Run project

```bash
php -S localhost:4000 -t public
```

### Change database

If you made a change to the models, use doctrine to update


```bash
php bin/doctrine orm:schema-tool:update
```
