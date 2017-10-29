 _______          __ http://amberworld.sourceforge.net __    __
|   _   |--------.  |--.-----.----.--.--.--.-----.----.  |--|  |
|       |        |  _  |  -__|   _|  |  |  |  _  |   _|  |  _  |
|___|___|__|__|__|_____|_____|__| |________|_____|__| |__|_____|
         AMBERWORLD - THE AMBERMOON OPEN SOURCE PROJECT

AMgfx v0.4 Copyright (c) 2003 by Oliver Gantert

This is a beta release of the Ambermoon graphics converter and is
intended for developers only. Currently there is no documentation
and you should have a look at the source code (amgfx.c) to see
what it does.


===== LICENSE =====

If you're going to use parts of the source code in your own
programs, you MUST give credit to the Amberworld project and
link to its website at http://amberworld.sourceforge.net


====== USAGE ======

AMgfx is completely configured from the command line. The only
argument that is required is the input file name. However you
can append one or more options to change the defaults.

    amgfx <filename> [options]

    Options:

    -out <filename>  Specify output filename, defaults to
                     source filename with ".tga" suffix.

    -b[pl] <num>     Number of bitplanes (1-6), default 5.

    -p[al] <num>     Palette number (0-49), default 49.

    -w[idth] <num>   Image width (4-640), default 16.

    -off <num>       Offset into file, default 0. Use this
                     option to skip the first <num> bytes
                     of the input file (if graphic data is
                     in the middle of some file).

    -s[ize] <num>    Size of file, default (filesize - offset).
                     Limits data block. Usually used
                     together with the -off option.

    Example:

    amgfx Portraits\001 -w 32 -p 32
    amgfx Layouts\002 -w 320 -b 3


===== HISTORY =====

v0.4 (2004-03-23) : Added a custom palette for portraits and items (thanks to Iceblizz)
v0.3 (2003-09-03) : More configurable, advanced options
v0.2 (2003-09-01) : First release
v0.1 (2003-08-28) : 'Inofficial' Win32 binary
