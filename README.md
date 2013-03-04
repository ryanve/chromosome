[loci](http://github.com/ryanve/loci)
====

an experimental PHP template engine that generates views based on content data stored in JSON files

### server setup

Add the following redirects to your root [.htaccess](http://en.wikipedia.org/wiki/Htaccess) file:

```
# github.com/ryanve/loci
# change _posts or _php as needed
<IfModule mod_rewrite.c>
  RewriteEngine On

  # If loci.php does not exist, skip the next 4 rules
  RewriteCond %{DOCUMENT_ROOT}/_php/loci.php !-f
  RewriteRule .* - [S=4]
  
  # OPTION 1: Unless file, add trailing slash
  # RewriteCond %{REQUEST_FILENAME} !-f
  # RewriteRule ^.*[^/]$ /$0/ [L,R=301]
  # OPTION 2: Unless dir, remove trailing slash
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)[/]+$ /$1 [L,R=301]
  
  # If not a file or dir, look in _posts and rewrite if it exists there
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{DOCUMENT_ROOT}/_posts/%{REQUEST_URI} -d
  RewriteRule ^(.*)$ /_php/loci.php?request=$1&from=_posts [L]
  
  # If not a file or dir, map direct file requests
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{DOCUMENT_ROOT}/_posts/%{REQUEST_URI} -f
  RewriteRule ^(.*)$ _posts/$1 [L]

  # If dir containing index.json, then rewrite
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteCond %{REQUEST_FILENAME}index.json -f
  RewriteCond %{REQUEST_URI} !^/?_posts/.+$
  RewriteRule ^(.*)$ /_php/loci.php?file=$1/index.json [L]
</IfModule>
```
