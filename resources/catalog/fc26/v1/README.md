# FC26 metadata catalog, revision 1

These files are the version-controlled source for the initial league and club metadata.
They are not edited through the application or directly in a database.

## Rules

- Keep `schema_version` and `game_edition` unchanged for this revision.
- Every item needs a committed UUIDv7 `id` and a unique, uppercase `code`.
- A `code` uses uppercase ASCII letters, digits, and hyphens only. It is permanent once released.
- `country` is the English display name used by the application.
- `logo_url` may be `null`; when supplied it must be a public media path no longer than 255 characters.
- A club's `default_league_id` is optional and must match a league UUID in `leagues.json`.
- Do not duplicate a club merely because it may play in a different league in a future save. The default league is only a creation-form suggestion.
- Never change or remove an item after this catalog revision has been released. Create a new catalog revision and data migration instead.

## League fields

`season_start_month`, `season_start_day`, `season_end_month`, and `season_end_day` define the default season dates used to prefill a new season. They use normal calendar numbers and must form a valid date in every year.

## Club fields

`default_league_id` is the club's initial league suggestion. It maps directly to the
`clubs.default_league_id` foreign key and does not constrain the league selected by
a user for a season.

## Workflow

1. Amend the catalog only through a deliberate new revision.
2. Validate JSON syntax and foreign-key references.
3. Review the resulting diff.
4. Commit the catalog together with its dedicated data migration.

This revision contains 33 playable men's domestic leagues and 592 clubs assigned
to those leagues.

International, continental, generic, free-agent, created-player, youth, Rest of
World, and women's competitions are deliberately excluded from this revision,
along with their clubs. They are not selectable Career Mode leagues in Manager Hub.
Every league currently uses the provisional 1 July to 30 June calendar. Logo URLs
use the `/media/catalog/fc26/...` convention; the image files themselves are
intentionally not committed to Git. Restore them from the operational backup when
setting up an environment. Review the calendar exceptions before the initial data
migration is created.
