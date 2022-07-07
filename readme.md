# BerlinDB Rest WIP

This code is based on a modded [WordPress-example](https://github.com/berlindb/wordpress-example) and [BerlinDB](https://github.com/berlindb/core).  

The discussion is on https://github.com/berlindb/core/issues/26.

When the prototype is working we can update both the repository with those changes with 2 different pull requests.

Changes are wrapped on `// EDIT New lines` and the Rest.php file is the integration.

## Changes

* Added a new array variable `rest` in [`Schema`](https://github.com/Mte90/BerlinDB-Rest/blob/master/core/src/Database/Schema.php) class to set the global proprierty for the table, it is used to create a new item
* A new class [`Rest`](https://github.com/Mte90/BerlinDB-Rest/blob/master/core/src/Database/Rest.php)that is executed once for the table and for every column, it will check if rest is enabled and will enable the various endpoints (check that file for all the features)

### New filters

**books** is the table name so it will be replaced.

* `berlindb_rest_books_create`: executed on creation, the parameter shared is the items data in this way can be sanitized etc, if it is a WP_Error boject the creation is blocked
* `berlindb_rest_books_delete/search/update`: return a boolean to execute the action, 2 variables shared, the whole request and the whole Rest object
* `berlindb_rest_books_update/search_value`: executed on update, the parameter shared is the items data in this way can be sanitized etc, if it is a WP_Error object the creation is blocked

## Other information

* It is used the CRUD acronym to enable all the 4 kind of actions supported create/read/update/delete
* Supports all the parameters of the `Query` class
* Include a pagination support with the `page` parameter
* `shows_all` is a global parameter that enable a paginated list of all the items
* On search if it is used a column not enabled it will be removed, in case no columns is set it will use all the enabled ones

## Status

### Done (the url are just examples)

* `wp-json/books/<id>`|GET: Read endpoint by column/key name
* `wp-json/books/<id>`|DELETE: Delete item by column/key name
* `wp-json/books/<id>`|PUT: Update item by column/key name
* `wp-json/books/create`|POST: Create endpoint
* `wp-json/books/all`|GET: List endpoint with pagination and offset
* `wp-json/books/search/?s=word&search_columns[]=<title>`: Will search inside the `title` column that has that content

### Todo

**Specific changes are commented with TODO to discuss later**

* Improve error messages
  
## Test

To view in the browser `http://domain.test/wp-json/books/1` or to search `http://domain.test/wp-json/books/?s=Moc&search_columns[]=title`.

```
# Create
curl -X POST -d value['isbn']=val -d value['title']=val2 -d value['author']=val2 -d value['date_created']=1657128891 -d value['date_published']=867283220 http://domain.test/wp-json/books/create

# Delete
curl -X DELETE  http://boilerplate.test/wp-json/books/1

# Update
curl -X PUT -d value['isbn']=value -d value['title']=val3  http://boilerplate.test/wp-json/books/11
```