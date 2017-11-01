# php-touch-mpd-client

Another PHP based [MPD client](https://www.musicpd.org/). Could not find a touch screen MPD client I liked so I wrote my own.

I have this running on a Raspberry Pi 3 with my music files on a USB thumb drive. I could have put the files on the SD card but none of my SD cards have enough space for all of my music.

The onboard sound card of the Raspberry Pi 3 is not a true sound card so I installed a [Hifi-berry DAC+ Pro](https://www.hifiberry.com/products/dacplus/).

## Documentation

See the files in the [how_to_use](./how_to_use) folder.

## Screen Size

This MPD client was designed on a 7" 800x480 touch screen display. It will work on smaller screens but will need to be modified to look good. Using the interface on a larger screen will make the text easier to read but won't be much benefit other wise.

## Before You Begin

These instructions were written for the Raspberry Pi 3 running Raspbian Jessie Lite (Release date 2017-06-21) with its defualt user 'pi'.

As long as your running some version of Linux (Debian, Ubuntu, Fedora, Red Hat, etc), the only thing you should need to change in these instructions is the user name.

In my setup, the website and MPD server are run on the audio server. I use a 2nd Raspberry Pi running Raspbian OS with a desktop to connect to the website.

## Raspbian OS

[Download](https://www.raspberrypi.org/downloads/raspbian/) the most recent version of the Raspian OS and [write it](https://www.raspberrypi.org/documentation/installation/installing-images/README.md) to the SD card. I use the lite version on my servers since they are run [headless](https://en.wikipedia.org/wiki/Headless_computer).

I use openssh and rsync to work on my servers so these programs will need to be installed on the computer you are using to setup your audio server. They are already installed in Raspbian OS.

These instructions assume your going to store your music on the SD card so make sure you have one with enough storage space. For most people, a 32G SD card will have enough space for their music and Raspbian OS. For reference, I've got over 3,500 songs and a couple of audio books that use 18G of storage space on my USB thumb drive.

If the music files are going to be stored on a USB drive or network server, you will need to adjust these instructions accordingly.

## Dependencies

It has been a while since I installed the required software so I might be missing a package or two.

Install the webserver (apache2), PHP support and the Pulseaudio software.

`sudo apt-get install apache2 apache2-suexec-custom php5 pulseaudio`

To use the included song_api.php file to retrieve song lyrics and album cover art from another server, you will need php5-curl and php5-json.

`sudo apt-get install php5-curl php5-json`

For more details on how to use the song_api.php file, see the [readme](./songapi) located in the /songapi folder.

## Audio storage

The easiest storage method is to copy your music files onto the SD card. 

### Directory/Folder Layout

There is no right or wrong way to organize your music. I have my music files sorted by artist and then by album.

My current directory layout:

    /home/pi
        /music
            /playable
            /playlists

The /playable directory is where the audio files will be stored.

The /playlists directory is where the playlists will be stored.

To create the above directory layout, run `mkdir -p ~/music/playable && mkdir ~/music/playlists`

### Getting Your Music Onto the Server

If you have any playlists in /home/pi/music/playable, you will need to move them to /home/pi/music/playlists so MPD can find and use them.

#### Via Rsync

I use ssh keys to connect to my servers so my rsync command looks like this one: `rsync --itemize-changes -avvP -e "/usr/bin/ssh -i /home/<username>/.ssh/id_rsa" /path/to/music/files pi@audioserver:~/music/playable`.

For password authentication, my rsync command would look like this one: `rsync --itemize-changes -avvP /path/to/music/files pi@audioserver:~/music/playable`.

#### Via USB Drive

Plug the USB drive into an available USB port on your computer (not the audio server).

Now open up a terminal and use one of the rsync commands above.

## Website

### Clone repository

Create the ~/public_html folder and make it the working directory.

`mkdir ~/public_html && cd ~/public_html`

Now clone this repository with:

`git clone https://github.com/mysmartbus/php-touch-mpd-client.git .`

The trailing `.` puts the contents of the repository into the current directory instead of putting the contents into ./php-touch-mpd-client which saves us a few steps.

### Configure Apache HTTPD

There is only one file that needs to be edited.

`sudo nano /etc/apache2/sites-enabled/000-default.conf`

Since my audio server will only be host a single website, I removed all of the lines already in the file and typed in the \<VirtualHost> block below.

    <VirtualHost *:80>

        ServerName mpc.example.com
        DocumentRoot /home/pi/public_html

        <Directory /home/pi/public_html>
            DirectoryIndex index.php index.html
            AllowOverride none
            Require all granted
        </Directory>

    </VirtualHost>

Press Ctrl+x key to exit. Press y. Press enter.

Now restart the HTTPD server with `sudo service apache2 restart`.

__Note__: To be able to access your audio server via the ServerName listed above, you will need to add an entry to your routers DNS forwarder using the ServerName and IP address of your audio server. Alternatively, you can add the IP address and ServerName to your computers [/etc/hosts](http://man7.org/linux/man-pages/man5/hosts.5.html) file.

__NOTE 2__: To find the IP address of your audio server, visit http://ipecho.net/localip.html

## MPD (Music Player Daemon)

MPD is the program that will play the music files.

`sudo apt-get install mpd`

### Configure MPD

Create the directory where MPD will store is configuration and settings files.

`mkdir ~/.mpd`

Create mpd.conf

`nano ~/.mpd/mpd.conf`

Copy or type in the following lines

    music_directory         "/home/pi/music/playable"
    playlist_directory      "/home/pi/music/playlists"
    db_file                 "/home/pi/.mpd/tag_cache"
    log_file                "/home/pi/.mpd/mpd.log"
    pid_file                "/home/pi/.mpd/pid"
    state_file              "/home/pi/.mpd/state"
    sticker_file            "/home/pi/.mpd/sticker.sql"
    
    user                    "pi"
    bind_to_address         "localhost"
    port                    "6600"
    
    audio_output {
        type            "pulse"
        name            "My Pulse Output"
        sink            "\<sink_name>"
    }
    
    filesystem_charset              "UTF-8"
    id3v1_encoding                  "UTF-8"

To find the correct sink name, run `pactl list sinks short` and look for a long string similar to `alsa_output.platform-soc_sound.analog-stereo`.

### Playlists

MPD by default has support for .m3u formatted playlists. Other formats are [supported via plugins](https://www.musicpd.org/doc/user/playlist_plugins.html).

## Starting PulseAudio and MPD

### Using the Command Line

#### PulseAudio

Command: `/usr/bin/pulseaudio --start --system=false`

This will start PulseAudio if it is not already running and is guaranteed to have PulseAudio fully initialized when this call returns. Using `--system=false` puts PulseAudio into per-user mode.

#### MPD

Command: `/usr/bin/mpd /home/pi/.mpd/mpd.conf`

Wait a few seconds and you will be returned to the command prompt.

### Using a Startup Script

MPD and Pulseaudio do not have an init.d or upstart script that I know of. Pulseaudio can be started automatically but I've never gotten it to work reliably so I start it manually.

After rebooting the audio server, I have to log in and run the script below to get everything going again.

I put this script in `/home/pi/start_server.sh`.

    #!/bin/bash
    
    # File: start_server.sh
    # Description: Starts the pulseaudio and mpd programs so the audio server can serve audio.
    
    # Start pulseaudio
    #
    # --start
    #    Start PulseAudio if it is not running yet. This is different from starting PulseAudio without --start which would fail if PA is already running.
    #    PulseAudio is guaranteed to be fully initialized when this call returns. Implies --daemonize.
    echo "Starting Pulseaudio"
    /usr/bin/pulseaudio --start --system=false
    
    # Give pulseaudio time to settle
    sleep 1
    
    # Start mpd
    echo "Starting MPD"
    /usr/bin/mpd /home/pi/.mpd/mpd.conf

After running the script, I can log out and control MPD using the web interface.

## Testing

Open up a browser, type in the server name you selected when you configured Apache HTTPD and you should see the Playqueue tab with an empty queue.
