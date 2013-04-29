<?php
require '../basepedia.php';

$ids = isset( $_GET['ids'] ) ? explode( '|', rawurldecode( $_GET['ids'] ) ) : array();
$sites = isset( $_GET['sites'] ) ? explode( '|', rawurldecode( $_GET['sites'] ) ) : array();
$titles = isset( $_GET['titles'] ) ? explode( '|', rawurldecode( $_GET['titles'] ) ) : array();
$languages = isset( $_GET['languages'] ) ? explode( '|', rawurldecode( $_GET['languages'] ) ) : array();

$formatId = 'text/html';
if( isset( $_GET['format'] ) && $_GET['format'] !== '' ) {
	$formatId = str_replace( ' ', '+', urldecode( $_GET['format'] ) );
} elseif( function_exists( 'http_negotiate_content_type' ) ) {
	$acceptedFormats = array( 'text/html' => 'text/html' );
	foreach( EasyRdf_Format::getFormats() as $format ) {
		foreach( $format->getMimeTypes() as $format => $priority ) {
			$acceptedFormats[$format] = $format;
		}
	}
	$formatId = http_negotiate_content_type( $acceptedFormats, $_SERVER['HTTP_ACCEPT'] );
}

$basePedia = BasePedia::singleton();

if( count( $titles ) > 50 || count( $ids ) > 50 ) {
	header( 'HTTP/1.1 409 Conflict' );
	echo 'You have asked for too many pages.';
	exit();
} elseif( $ids === array() ) {
	if( $sites === array() || $titles === array() ) {
		if( in_array( $formatId, array( 'html', 'text/html' ) ) ) {
			header( 'Content-type: text/html' );
			echo file_get_contents( 'help.html' );
		} else {
			header( 'HTTP/1.1 404 Not Found' );
			echo 'No entities found.';
		}
		exit();
	} else {
		$basePedia->addEntitiesFromSitelinks( $sites, $titles, $languages );
	}
} else {
	if( $sites === array() && $titles === array() ) {
		$basePedia->addEntitiesFromIds( $ids, $languages );
	} else {
		header( 'HTTP/1.1 409 Conflict' );
		echo 'Either provide the item "id" or pairs of "site" and "title" for a corresponding page.';
		exit();
	}
}

if( $basePedia->isEmpty() ) {
	header( 'HTTP/1.1 404 Not Found' );
	echo 'No entities found.';
	exit();
}

$graph = $basePedia->getRdfGraph( $languages );

if( in_array( $formatId, array( 'html', 'text/html' ) ) ) {
	header( 'Content-type: text/html' );
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>BasePedia</title></head><body>';
	echo $graph->dump( true );
	echo '</body></html>';
} else {
	try {
		$format = EasyRdf_Format::getFormat( $formatId );
		header( 'Content-type: ' . $format->getDefaultMimeType() );
		echo $graph->serialise( $format );
	} catch( Exception $e ) {
		header( 'HTTP/1.1 409 Conflict' );
		echo 'Unknown serialisation format ' . $formatId;
	}
}
