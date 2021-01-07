# shorten

This is an image hosting service which generates URLs in the form of `/random_word/random_word`.  
I no longer use it and it is here for archival purposes.

## Features

- Upload images, URLs (with auto forwarding), text or any other type of file
- Automatic MIME-Type detection
- Link "renaming" support
- Links expire after they remain unused for a set duration, this duration can be specified per upload
- Single-use URLs by setting the expiry duration to 0
- Multiple user and site support
- Ability to add custom words in the wordlist
- Can act as a custom upload destination for [ShareX](https://getsharex.com/)

## Requirements

You need a [LAMP](https://en.wikipedia.org/wiki/LAMP_(software_bundle)).

## Setup

1. Import the DB Schema from `db.sql`
2. Setup the necessary config for the database and users in `common.php`
3. Configure your screenshot tool to upload files (see REST API section)


## REST API

### Authorization

Simply add the POST parameters for "name" and "key" to authenticate as the corresponding user.  
Users are configured in `common.php`.

### POST `/upfile.php`

Uploads a file. Returns the shortend URL.

Required parameters are "name", "key", "expiry" in POST body, as well as a file.

"expiry" is the time after the first visit, when the link expires. This timer is reset after each subsequent visit. "expiry" is parsed with [`strtotime`](https://www.php.net/manual/en/function.strtotime.php) (e. g. "+2 days")


### POST `/uplink.php`

Uploads a link. Returns the shortend URL.

Required parameters are "name", "key", "expiry" and "link" in POST body.

"expiry" is the time after the first visit, when the link expires. This timer is reset after each subsequent visit. "expiry" is parsed with [`strtotime`](https://www.php.net/manual/en/function.strtotime.php) (e. g. "+2 days")

"link" is the link to forward to.


### GET `/redirect.php`

Returns the file, or redirects to the destination link of the URL.

Required parameters are "id" and "pass".

The .htaccess file included in this repository converts any URL in the form of `/<id>[/<pass>][.<extension>]` to `redirect.php?id=<id>&pass=<pass>`.
