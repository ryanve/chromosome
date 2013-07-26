# [chromosome](../../)

Chromosome (formerly Loci) is a minimal PHP template engine that generates views based on content data stored in JSON files.

## Setup

Add the following [Apache](http://httpd.apache.org/docs/2.0/misc/rewriteguide.html) rewrites to the root [.htaccess](http://en.wikipedia.org/wiki/Htaccess) file on your server. Change `_items` or `_php` paths as needed to accommodate your directory structure.

```apache
# BEGIN Chromosome
# loci.airve.com
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
</IfModule>
# END Chromosome
```

## License

### [Chromosome](../../) is available under the [MIT license](http://en.wikipedia.org/wiki/MIT_License)

Copyright (C) 2013 by [Ryan Van Etten](https://github.com/ryanve)