### [airve](https://github.com/airve)/[loci](https://github.com/airve/loci)

Loci is a lightweight PHP template engine that generates views based on content data stored in JSON files.

### server setup

Add the following rewrites to your root [.htaccess](http://en.wikipedia.org/wiki/Htaccess) file:

```
# BEGIN LOCI
# loci.airve.com
# Change _items or _php as needed
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
  RewriteCond %{DOCUMENT_ROOT}/_php/airve/loci -d
  RewriteRule ^(.*)$ /_php/airve/loci/request.php?request=$1&from=_items [L]

  # Map direct file requests.
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{DOCUMENT_ROOT}/_items/%{REQUEST_URI} -f
  RewriteRule ^(.*)$ _items/$1 [L]
</IfModule>
# END LOCI
```