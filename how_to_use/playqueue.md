# Play Queue Tab

This is the main tab. It displays info on the currently playing song. Also listed are the 4 preceeding and 4 following songs from the play queue.

---

[Screenshot of Play Queue tab](./playqueue_tab.png)

1. Audio track title and artist/band name  
 Tap or click on the track name to go to the [Song Info](./songinfo.md) tab  
 Tap or click on the artist/band name to display the albums and songs in MPDs [database](./database.md) for the artist/band.
2. The button titled '0:00' restarts the audio file from the beginning.
3. Play control buttons (Left to right)  
  A. Previous track button  
  B. Pause/Play current track  
  C. Next track button  
  D. Stop playing
4. Volume control (Left to right)  
  A. Decrease volume  
  B. Mute/Unmute audio  
  C. Increase volume  
  D. Current volume level from 0-100%  
   Tapping this button will do one of two actions.
   1. Mute the audio. Audio track will continue playing.
   2. Unmute the audio and set the volume level to the value set in the [config file](./configfile.md).
5. Mode controls  
 A. Random - Defaults to on. Plays a random song from the current playlist. If turned off, MPD will play the songs in the order listed.  
 B. Consume - Defaults to off. If turned off, the song will be removed from the play queue after it has been played one time.  
 C. Repeat - Defaults to off. Turn on to loop through the current play queue until someone hits the stop button.
6. Lists the current song and the 4 preceeding and 4 following songs.
7. The current song. Will always be in the middle of the list unless it is the first or last song of the play queue.
