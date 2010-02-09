<?php

class ReferenceMovie
{
	# Technical reference: http://developer.apple.com/mac/library/documentation/QuickTime/QTFF/QTFFChap2/

	# PHP5
	#private $movies = "";
	# PHP4
	var $movies = "";

	#
	# Adds a new movie URL
	#
	# $url     - URL where the movie is located
	# $speed   - Bitrate of the stream
	# $quality - Quality of the stream. If equal speed, chooses the one with highest quality first
	#            Higher number, better quality
	#
	# PHP5
	#public function addMovie($url, $speed, $quality)
	# PHP4
	function addMovie($url, $speed, $quality)
	{
	# URL
	$urlSize = strlen($url) + 1; # 1 end byte
	$urlSizeString = sprintf("%c%c%c%c",
		(($urlSize >> 24) & 0xff),
		(($urlSize >> 16) & 0xff),
		(($urlSize >> 8) & 0xff),
		($urlSize & 0xff));
	$urlString = sprintf("\0\0\0\0url %s%s\0", $urlSizeString, $url);

	$dataRefSize = strlen($urlString) + 4 + 4;
	$dataRefSizeString = sprintf("%c%c%c%c",
		(($dataRefSize >> 24) & 0xff),
		(($dataRefSize >> 16) & 0xff),
		(($dataRefSize >> 8) & 0xff),
		($dataRefSize & 0xff));
	$dataRefString = sprintf("\0\0\0%crdrf%s", $dataRefSize, $urlString);

	# Datarate
	$speedString = sprintf("%c%c%c%c",
		(($speed >> 24) & 0xff),
		(($speed >> 16) & 0xff),
		(($speed >> 8) & 0xff),
		($speed & 0xff));
	$dataSpeedSize = 16;
	$dataSpeedString = sprintf("\0\0\0%crmdr\0\0\0\0%s",
		$dataSpeedSize, $speedString);

	# Quality
	$qualityString = sprintf("%c%c%c%c",
		(($quality >> 24) & 0xff),
		(($quality >> 16) & 0xff),
		(($quality >> 8) & 0xff),
		($quality & 0xff));
	$dataQualitySize = 12;
	$dataQualityString = sprintf("\0\0\0%crmqu%s", $dataQualitySize, $qualityString);

	$descSize = strlen($dataRefString) + strlen($dataSpeedString) + 4 + 4;
	$descSizeString = sprintf("%c%c%c%c",
		(($descSize >> 24) & 0xff),
		(($descSize >> 16) & 0xff),
		(($descSize >> 8) & 0xff),
		($descSize & 0xff));
	$descString = sprintf("\0\0\0%crmda%s%s", $descSize, $dataRefString,
		$dataSpeedString);

	$this->movies = $this->movies . $descString;
	}

	#
	# Returns the reference movie
	#
	# PHP5
	#public function printMovie()
	# PHP4
	function printMovie()
	{
	$refSize = strlen($this->movies) + 4 + 4;
	$refSizeString = sprintf("%c%c%c%c",
		(($refSize >> 24) & 0xff),
		(($refSize >> 16) & 0xff),
		(($refSize >> 8) & 0xff),
		($refSize & 0xff));
	$refString = sprintf("%srmra%s", $refSizeString, $this->movies);

	$movSize = strlen($refString) + 4 + 4;
	$movSizeString = sprintf("%c%c%c%c",
		(($movSize >> 24) & 0xff),
		(($movSize >> 16) & 0xff),
		(($movSize >> 8) & 0xff),
		($movSize & 0xff));
	$movString = sprintf("%smoov%s", $movSizeString, $refString);

	return  $movString;

	}

}

#
# EXAMPLE USE
#

preg_match("/([^\/]*)\.mov$/", $_SERVER['REQUEST_URI'], $match);
$url = $match[1];

$movie = new ReferenceMovie();
$movie->addMovie("rtsp://darwin.mydomain.com/".$url.".sdp", 76800, 100);
# One can add several more URLs, for different speeds
#$movie->addMovie("http://darwin.mydomain.com/".$url.".sdp", 76800, 100);

header("Content-Type: video/quicktime");
echo $movie->printMovie();
?>
