# WoW Forever database

Run the Laravel migration against the site's configured database. The four
`wow_*` tables are isolated from the rest of the application. Imports run
through Artisan and do not add public routes.

Export the Auctionator account SavedVariables file after logging out or
reloading WoW. Export files belong outside the web root and outside Git.

```sh
python3 tools/export-auctionator.py /path/to/Auctionator.lua > /private/path/auctionator.json
php artisan wow:import /private/path/auctionator.json
```

The importer saves each market and every cached item with its latest recorded
buyout in copper and Auctionator's full per-item history object. The capture
timestamp is the SavedVariables file modification time. Prices are cached
observations and the database has no information about listings Auctionator
never recorded. This exporter reads the v8 serialized format used by the
local Forever installation.

A character import uses this JSON format. Include only values observed in the
game. A snapshot may contain recipes before character stats are available.

```json
{
  "source": "character",
  "captured_at": "2026-11-16T14:41:00Z",
  "realm": "Nightstorm",
  "character": "Darvor",
  "stats": {},
  "recipes": {
    "Leatherworking": ["Hillman's Cloak"]
  }
}
```

Run `php artisan wow:import /private/path/character.json` to load it.
Re-importing the same file skips it. A new capture is stored alongside older
captures. A partial recipe list does not delete any earlier records.

## Deploy with Laravel Forge

In Forge, verify that the site for `douglascockerham.com` tracks the GitHub
repository and branch where the migration and importer will be released.
Deploy that version using the site's existing deployment script.

If the deployment script already runs migrations, confirm this migration ran
with `php artisan migrate:status`. Otherwise, run the following from the
active application directory on the Forge server using the site's PHP version:

```sh
php artisan migrate --path=database/migrations/2026_11_17_000001_create_wow_data_tables.php --force
php artisan wow:import /private/path/auctionator.json
php artisan wow:import /private/path/character.json
```

Transfer the two exports over SSH to a directory outside the site's web root
first. The exports contain the initial market data and recipe list. Keep them
out of the GitHub repository.
