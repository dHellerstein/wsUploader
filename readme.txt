7 March Feb 2022: wsUploader: upload many files from a source http server to a target http server. 

wsUploader is meant to be installed in two locations: a 'source http server' and a 'target http server'.
In both cases, the wsUploader.zip file should be extracted to its own directory.

  *  When unzipping, a temp sub directory is created.
  *  wsUploader uses jQuery -- version 3.6.0 is included in the installation (.zip) file.
  *  wsUploader uses several wsurvey.lib javascript and php libraries, which are also included in the .zip file.

After installation, the following MUST be modified
  *  On the source server: wsUploader.php
  *  On the target server: wsUploaderTarget.php

See wsUploader_desc.htm for the details

Caution: 
  wsUploader has a few security features, but they are NOT strong.
  In particular, if you can not control access to wsUploader.php and wsUploaderTarget.php -- it is RISKY to install wsUploader.
  Which is to say -- it is NOT recommended!

  For further details: see the Caution section of wsUploader_desc.htm
 
  -------------------

Contact: Daniel Hellerstein. danielh@crosslink.net. 
       http://www.wsurvey.org/distrib, or  https://github.com/dHellerstein 

Disclaimer:

    wsUploader is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    wsUploader is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    If you did not receive a copy of the GNU General Public License,
    see <http://www.gnu.org/licenses/>.

Daniel Hellerstein, danielh@crosslink.net
