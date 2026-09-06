# Persistent media for Manager Hub

Catalog badges are operational media, not source-controlled application assets.
The catalog stores a relative public URL such as
`/media/catalog/fc26/clubs/<uuid>.png`.

## Local development

Restore the separately backed-up media tree into `public/media`. The directory is
ignored by Git and is never part of application source.

## Production

Create a persistent directory outside the deployment release, for example
`/var/lib/managerhub/media`, then restore the backed-up media tree there.

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

Later deployments reuse the same directory and never need the catalog media
source files.

## Operational rules

- Do not commit files below `public/media`.
- Missing images remain `null` or use the UI fallback; they must never prevent a
  catalog import or page render.
- Player images follow the same media path convention but must be cached only
  when needed, never imported for the entire player catalog.
