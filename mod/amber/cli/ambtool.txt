 _______          __ http://amberworld.sourceforge.net __    __
|   _   |--------.  |--.-----.----.--.--.--.-----.----.  |--|  |
|       |        |  _  |  -__|   _|  |  |  |  _  |   _|  |  _  |
|___|___|__|__|__|_____|_____|__| |________|_____|__| |__|_____|
         AMBERWORLD - THE AMBERMOON OPEN SOURCE PROJECT

AMBtool v0.6 Copyright (c) 2004 by Oliver Gantert

This is a beta release of the AMB file decoder and is intended
for developers only. Currently there is no documentation
and you should have a look at the source code to see what it does.

Please note that there isn't much error checking, so the program
might crash if you use it on unsupported or corrupted files.

====== BUILD ======

Before compiling the sources, please check am_os.(c|h) to make
sure your system is recognized correctly. You may need to
define either WIN32 or AMIGA, for other systems some functions
have to be adapted.

===== LICENSE =====

If you're going to use parts of the source code in your own
programs, you MUST give credit to the Amberworld project and
link to its website at http://amberworld.sourceforge.net

===== HISTORY =====

v0.6  (2004-03-31) : AMBlib 0.5 (AMBR/AMPC support)
v0.5a (2004-03-19) : JH decryption compatibility fix
v0.5  (2004-03-18) : Uses AMBlib, AMtool renamed to AMBtool
v0.4  (2003-09-01) : Auto decompress LOB from archive
v0.3a (2003-08-29) : Directory creation on Win32 changed
v0.3  (2003-08-28) : Bug in LOB decompression fixed
v0.2a (2003-08-28) : Amiga binary
v0.2  (2003-08-27) : First beta release and Win32 binary
