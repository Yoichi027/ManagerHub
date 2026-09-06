# Persistent media for Manager Hub

Catalog badges are operational media, not source-controlled application assets.
The catalog stores a relative public URL such as
`/media/catalog/fc26/clubs/<uuid>.png`.

## Local development

The importer defaults to `public/media`. The directory is ignored by Git, so it
can be regenerated without changing the application source.

Run a dry run first:

```powershell
python tools/import_fc26_catalog_media.py
```

Download the catalog assets and update the catalog URLs only after the files
were written successfully:

```powershell
python tools/import_fc26_catalog_media.py --apply --update-catalog
```

## Production

Create a persistent directory outside the deployment release, for example
`/var/lib/managerhub/media`, and configure the deployment environment:

```text
MANAGERHUB_MEDIA_PATH=/var/lib/managerhub/media
MANAGERHUB_MEDIA_BASE_URL=/media
```

Serve that directory directly from Nginx. The application must not handle these
files through PHP.

```nginx
location /media/ {
    alias /var/lib/managerhub/media/;
    try_files $uri =404;
    access_log off;
    expires 30d;
}
```

Run the importer once on the server after the persistent directory and Nginx
rule exist. Later deployments reuse the same directory.

## Operational rules

- Do not commit files below `public/media`.
- Do not run the importer from a web request or background job.
- Missing images remain `null` or use the UI fallback; they must never prevent a
  catalog import or page render.
- Player images follow the same media path convention but must be cached only
  when needed, never imported for the entire player catalog.
