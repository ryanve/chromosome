## [Chromosome](../../) PHP package

- Generate views from content stored in static JSON and HTML files.
- Use as server-side template engine or as local site generator.

## Setup

### Apache

Add the following [Apache](http://httpd.apache.org/docs/2.0/misc/rewriteguide.html) rewrites to the root [.htaccess](http://en.wikipedia.org/wiki/Htaccess) file on your server. Change `_items` or `_php` paths as needed to accommodate your directory structure.

```apache
# BEGIN chromosome
# github.com/ryanve/chromosome
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Option 1: Unless file, add trailing slash.
  # RewriteCond %{REQUEST_FILENAME} !-f
  # RewriteRule ^.*[^/]$ /$0/ [L,R=301]
  # Option 2: Unless dir, remove trailing slash.
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)[/]+$ /$1 [L,R=301]
  
  # Search _items for non-existing URIs and rewrite if it exists there.
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{DOCUMENT_ROOT}/_items/%{REQUEST_URI} -d
  RewriteCond %{DOCUMENT_ROOT}/_php/chromosome -d
  RewriteRule ^(.*)$ /_php/chromosome/request.php?request=$1&from=_items [L]

  # Map direct file requests.
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{DOCUMENT_ROOT}/_items/%{REQUEST_URI} -f
  RewriteRule ^(.*)$ _items/$1 [L]
  
  # Rewrite other dirs that contain the JSON file.
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteCond %{REQUEST_FILENAME}item.json -f
  RewriteCond %{REQUEST_URI} !^/?_items/.+$
  RewriteRule ^(.*)$ /_php/chromosome/request.php?request=$1 [L]
</IfModule>
# END chromosome
```

## License

MIT