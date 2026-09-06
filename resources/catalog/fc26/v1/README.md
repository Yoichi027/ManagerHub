# FC26 metadata catalog, revision 1

These files are the version-controlled source for the initial league and club metadata.
They are not edited through the application or directly in a database.

## Rules

- Keep `schema_version` and `game_edition` unchanged for this revision.
- Every item needs a committed UUIDv7 `id` and a unique, uppercase `code`.
- A `code` uses uppercase ASCII letters, digits, and hyphens only. It is permanent once released.
- `country` is the English display name used by the application.
- `logo_url` may be `null`; when supplied it must be an absolute HTTPS URL no longer than 255 characters.
- A club's `default_league_code` is optional and must match a code in `leagues.json`.
- Do not duplicate a club merely because it may play in a different league in a future save. The default league is only a creation-form suggestion.
- Never change or remove an item after this catalog revision has been released. Create a new catalog revision and data migration instead.

## League fields

`season_start_month`, `season_start_day`, `season_end_month`, and `season_end_day` define the default season dates used to prefill a new season. They use normal calendar numbers and must form a valid date in every year.

## Club fields

`default_league_code` is the club's initial league suggestion. It does not create a permanent club-to-league relationship and it does not constrain the league selected by a user for a season.

## Workflow

1. Add or amend the catalog items locally.
2. Validate the JSON and references with the catalog validation command when it is introduced.
3. Review the resulting diff.
4. Commit the catalog together with its dedicated data migration.

The two existing entries are format examples, not an intended complete catalog.
