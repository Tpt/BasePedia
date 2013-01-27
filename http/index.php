<?php
require '../basepedia.php';

$ids = isset( $_GET['ids'] ) ? explode( '|', rawurldecode( $_GET['ids'] ) ) : array();
$sites = isset( $_GET['sites'] ) ? explode( '|', rawurldecode( $_GET['sites'] ) ) : array();
$titles = isset( $_GET['titles'] ) ? explode( '|', rawurldecode( $_GET['titles'] ) ) : array();
$languages = isset( $_GET['languages'] ) ? explode( '|', rawurldecode( $_GET['languages'] ) ) : array();
$formatId = isset( $_GET['format'] ) ? str_replace( ' ', '+', urldecode( $_GET['format'] ) ) : 'html';
$basePedia = new BasePedia();

if( $ids === array() ) {
	if( $sites === array() || $titles === array() ) {
		header( 'HTTP/1.1 404 Not Found' );
		echo 'No entities found.';
	} else {
		$basePedia->addEntitiesFromSitelinks( $sites, $ids, $languages );
	}
} else {
	if( $sites === array() || $titles === array() ) {
		$basePedia->addEntitiesFromIds( $ids, $languages );
	} else {
		header( 'HTTP/1.1 409 Conflict' );
		echo 'Either provide the item "id" or pairs of "site" and "title" for a corresponding page.';
	}
}
$graph = $basePedia->getRdfGraph( $languages );

if( $formatId === 'html' ) {
	echo $graph->dump( true );
} else {
	try {
		$format = EasyRdf_Format::getFormat( $formatId );
		header('Content-type: ' . $format->getDefaultMimeType() );
		echo $graph->serialise( $format );
	} catch( Exception $e ) {
		header( 'HTTP/1.1 409 Conflict' );
		echo 'Unknown serialisation format.';
	}
}
