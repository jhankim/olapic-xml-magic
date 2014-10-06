olapic-xml-magic
================

AngularJS + PHP powered script to validate + visualize product feed structure

## Dependencies

- NPM
- Bower
- Grunt
- PHP

## Install

1. Clone this repo:

  ```sh
  $ git clone git@github.com:jaepanda/olapic-xml-magic.git
  $ cd olapic-xml-magic
  ```

2. Run `npm install`

  ```sh
  $ npm install
  ```

3. Run `bower install`
  
  ```sh
  $ bower install
  ```
  
4. Serve the page using `grunt serve`

  ```sh
  $ grunt serve
  ```
  
5. Start validating at `http://localhost:9000/` :)

## Extras

### Max file upload size

This is currently set to `10M` in `/app/php.ini`.

You can alter the content of the `php.ini` to accomodate larger files (no safety guaranteed though!)

Example:
```
upload_max_filesize = 256M
post_max_size = 256M
```

## TODO

- [ ] Add better cURL handling
- [ ] Support authentication method for secure URL endpoints
- [ ] Better error handling & output
- [ ] Make UX prettier
