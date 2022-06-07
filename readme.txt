18 March 2022: wsUploader: upload many files from a source http server to a target http server.

wsUploader is meant to be installed in two locations: a 'source http server' and a 'target http server'.
  On the source server, wsUploader should be extracted to its own directory.
  On the target server, you can also extract wsUploader to its own directory. Or, just extract wsUploaderTarget.php.

  *  When unzipping, a number of files are extracted, and a temp sub directory is created.
  *  For most unzip software, this will be installed into a wsUploader subdirectory (under
     whatever directory the .zip file was placed).
  *  wsUploader uses jQuery -- version 3.6.0 is included in the installation (.zip) file.
  *  wsUploader uses several wsurvey.lib javascript and php libraries, which are also included in the .zip file.

Note: the "target" server ONLY needs wsUploaderTarget.php.  To save space, you can just install wsUploaderTarget.php
      to an appropriate directory (say, one with your favorite php utilities).

After installation, the following MUST be modified
  *  On the source server: wsUploader.php
  *  On the target server: wsUploaderTarget.php

See wsUploader_desc.htm for the details

Run time errors

   The following lists a few errors that may occur when using wsUploader (typically soon after wsUploader.php is loaded)

  * Unable to connect to target

	Is the targetUri correctly specified -- a fully qualified URL (ending with wsUploaderTarget.php) must be used
	Does the targetUri exist on the server?
        Maybe there is an error on the target server (is it running)?

  *  "Not Found. The requested URL was not found on this server."

     Is the targetUri correctly specified -- a fully qualified URL (ending with wsUploaderTarget.php) must be used
     Is the target server properly setup? Perhaps there is an error in virtual host definition?

  * "Unrecognized source server (aSourceNickame)....   "

     Is the sourceNickname (in wsUploader.php on the source server) have a matching entry in $targetDirs['aSourceNickname']
     (in wsUploaderTarget.php on the target server);

  *  "Error: the default source directory (aDirectoryPath) does not exist. Please modify ..."

       Does the $sourceDir does not refer to an existing directory (its value should be a fully qualified directory --
      and be sure to use / and not \

  *  "Unable to validate with the target server (using http://): mismatch
      0,mismatch "

      Is the targetPassword (in wsUploader.php on the source server) the same as the targetPwd (in wsUploaderTarget.php on the target server)


 * wsUploader page appears, with  header box noting "
   "No such target directory: .... This script (.../wsUploaderTarget.php) needs to be modified. "

   Is the value of $targetDirs['aSourceNickname'] a fully qualified directory, that exists on the target server.
    wsUploaderTarget.php WILL create subdirectories under this, but (for security reasons) it will NOT create this base directory.
     

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
