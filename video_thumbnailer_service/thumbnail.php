<?php

/*
thumbnail.php

A proof of concept video thumbnailer web service, akin to the ImageManip service shipped 
with Fedora Commons. Very rough code so YMMV - if it fills your server with random videos, 
eats your cat, or forces a rift in the space-time continuum, don't come running to me.

Warning: uses a shell command and the 'exec()' function to invoke ffmpegthumbnailer. This 
might be a bad idea.


Created by Graeme West on 2010-11-30.

Copyright 2010 Glasgow Caledonian University. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY GLASGOW CALEDONIAN UNIVERSITY ``AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL GLASGOW CALEDONIAN UNIVERSITY OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the
authors and should not be interpreted as representing official policies, either expressed
or implied, of Glasgow Caledonian University.

*/

// some basic defaults
$vidName = 'video';
$format = 'png';
$width = 128;

$urlhash = '';


function save_url($inPath,$outPath)
{ //Download images from remote server
    $in=    fopen($inPath, "rb");
    $out=   fopen($outPath, "wb");
    while ($chunk = fread($in,8192))
    {
        fwrite($out, $chunk, 8192);
    }
    fclose($in);
    fclose($out);
}



if(!isset($_GET['video']))
{
	
	echo "Please specify an input file using the 'video' GET parameter.";
} else
{
	//we'll use a SHA-1 hash to identify the video's origin to enable caching.
	$urlhash = sha1($_GET['video']);

	if(isset($_GET['width']) && !empty($_GET['width']))
	{
		$width = $_GET['width'];
	}

	if(isset($_GET['format']) && !empty($_GET['format']) && ($_GET['format'] === 'jpeg' || $_GET['format'] === 'png'))
	{
		$format = $_GET['format'];
	}

	$thumbName = $urlhash . '-' . $width . '.' . $format;


	if(!file_exists($urlhash)) // if a file from the same URL is already saved, don't download it again. We really should have some kind of date-based staleness checking - or even better, using ETags on the URL.
	{
		save_url($_GET['video'], $urlhash);
	} else {
		error_log("we found that the video had already been downloaded");
	}
	
	if(file_exists($urlhash)){
		error_log("the vid definitely exists");
		if(file_exists($thumbName)){
			unlink($thumbName);
		}
		$command = escapeshellcmd("ffmpegthumbnailer -i ". $urlhash . " -o ". $thumbName . " -c ". $format . " -s " . $width);

		exec($command);

		if(file_exists($thumbName))
			{
				error_log("the thumbnail exists");
				header('Content-type: image/'.$format);
				$imageData = file_get_contents($thumbName);
				echo $imageData;

			} else {
				header('HTTP/1.1 500 Internal Server Error');
				echo "There was an error.";
			}
	}

} // end if(!isset($_GET['video'])) line 63


?>