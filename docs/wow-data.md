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

## Private dashboard

Set `WOW_DASHBOARD_OWNER_EMAIL` in the Forge site's environment to the email
on the owner's existing site account. Refresh Laravel's config cache if the
deployment uses one. Sign in and open `/wow`. Guests must sign in and any
other account is denied. With no owner configured the page stays closed.

Craft rankings cover learned recipes whose WoW Forever vendor values and
reagent quantities are in `config/wow.php`. Auction estimates deduct the
configured cut but do not estimate sale probability or lost deposits.
The default 5% cut is for a faction market. Resale estimates compare the
latest cached buyout with the median of at least two earlier recorded
daily prices for the same item and market. They do not establish completed
sales, available quantity at that price, or the time needed to resell.

## Battle.net connection check

In the Forge site environment, set `BLIZZARD_CLIENT_ID` and
`BLIZZARD_CLIENT_SECRET` to the values from your Battle.net API client.
Refresh the Laravel config cache if it is enabled. From the site's application
directory on the server, run:

```sh
php8.3 artisan wow:battle-net-check
```

The command defaults to Darvor–Nightstorm, the US API, and the Normal
ruleset. It reports authentication, profile/statistics/professions endpoint
status, and the available auction house IDs. It does not import data. Use
the output to confirm the character and identify the matching market before
adding synchronization. Forever's endpoint layout is based on observed API
behavior and may change.

After the connection succeeds, import the character in the site directory:

```sh
php8.3 artisan wow:battle-net-check --character=darvor-nightstorm --sync-character
```

Reload `/wow` to see available character stats, profession ranks and equipped
items. The sync retains the previously imported learned recipes and adds named
recipes explicitly reported as known by Battle.net. This command does not
change auction prices.
