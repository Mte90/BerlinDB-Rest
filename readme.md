# BerlinDB Rest WIP

This code is based on a modded [WordPress-example](https://github.com/berlindb/wordpress-example) and [BerlinDB](https://github.com/berlindb/core).  

The discussion is on https://github.com/berlindb/core/issues/26.

When the prototype is working we can update both the repository with those changes with 2 different pull requests.

Changes are wrapped on `// EDIT New lines` and the Rest.php file is the integration.

## Changes

* Added a new array variable `rest` in `Schema` class to set the global proprierty for the table, it is used to create a new item
* A new class `Rest` that is executed once for the table and for every column, it will check if rest is enabled and will enable the various endpoints (check that file for all the features)

## Other information

* It is used the CRUD acronym to enable all the 4 kind of actions supported create/read/update/delete
* The `search` parameter enabled in a specific column enable the searc by this value 
* `shows_all` is a global parameter that enable a paginated list of all the items

## Status

### Done (the url are just examples)

* `wp-json/books/<id>`|GET: Read endpoint by column/key name
* `wp-json/books/create`|POST: Create endpoint
* `wp-json/books/all`|GET: List endpoint with pagination and offset

### Todo

**Specific changes are comment with TODO to discuss later**

* Update/Delete endpoints
* Search endpoint
* How to get the table name? (to remove the hardcoded stuff)
* Merge `read` and `read_all` in a unique method
  
## Test

To view in the browser `http://domain.test/wp-json/books/1`.

```
# Create
curl -X POST -d value['isbn']=val -d value['title']=val2 -d value['author']=val2 -d value['date_created']=1657128891 -d value['date_published']=867283220 http://domain.test/wp-json/books/create
```