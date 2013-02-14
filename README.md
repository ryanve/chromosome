[loci](http://github.com/ryanve/loci)
====

an experimental PHP template engine that generates views based on content data stored in JSON files

### server setup

Add the following redirects to your root [.htaccess](http://en.wikipedia.org/wiki/Htaccess) file:

```
RewriteEngine On

# Check if request is for a directory
RewriteCond %{REQUEST_FILENAME} -d

# Check if there's an index.json file in that directory
RewriteCond %{REQUEST_FILENAME}index.json -f

# Pass the request to the controller - the path to loci
# can be absolute or relative to your root as show here:
RewriteRule ^(.*)$ /loci/loci.php?file=$1index.json [L]
```
