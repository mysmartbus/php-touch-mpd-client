# song_api.php Usage Instructions

The purpose of the song api is so that the Song Info tab can display the lyrics/transcription and cover art for the currently playing audio track.

The song api and database can be hosted on the same computer/server as the PHP client. You can also run everything ([the interface](../), [the song api](./song_api.php), [the database](./song_api_database.sql) and [MPD](https://www.musicpd.org/)) on a single computer.

## Database

An empty database is available in `song_api_database.sql`. It was generated on a MySQL 5.5 server but should work with newer versions of MySQL.

Currently, only a MySQL database schema is supplied. If you use a different database server ([MariaDB](https://mariadb.org/), [PostgreSQL](https://www.postgresql.org/), [SQLite](https://www.sqlite.org/), etc), you will need to convert the `song_api_database.sql` file into a format compatible with your server.

### Connecting to the database

The method and PHP code used will vary according to your personal prefferences.

Add your database connection code to `song_api.php` near line 133.

When setting up database connections, I use $dbc as the handle for the connection. You will need to update all lines that contain $dbc to match the functions used by your database connection.

## cl_Validation.php

Does some sanity checks on $_POST and $_GET variables.

This class also is used to store/display custom warning and error messages when something did not work the way it was supposed to.

The something could be a bad/failed database call, a missing file called by include(), a permissions error when trying to access a restricted page or some other problem that the user needs to be made aware of.

Keep this file in the same folder as `song_api.php` to simplify setup. If you want to put `cl_Validation.php` in a different folder, you will need to update the `require ...` line near line 120 to reflect the correct path to `cl_Validation.php`.
