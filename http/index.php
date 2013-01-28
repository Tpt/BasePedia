<?php
require '../basepedia.php';

$ids = isset( $_GET['ids'] ) ? explode( '|', rawurldecode( $_GET['ids'] ) ) : array();
$sites = isset( $_GET['sites'] ) ? explode( '|', rawurldecode( $_GET['sites'] ) ) : array();
$titles = isset( $_GET['titles'] ) ? explode( '|', rawurldecode( $_GET['titles'] ) ) : array();
$languages = isset( $_GET['languages'] ) ? explode( '|', rawurldecode( $_GET['languages'] ) ) : array();
$formatId = isset( $_GET['format'] ) ? str_replace( ' ', '+', urldecode( $_GET['format'] ) ) : 'html';
$wgBasePediaRepo = isset( $_GET['repo'] ) ? $_GET['repo'] : 'wikidata.org';
if( !in_array( $wgBasePediaRepo, array( 'wikidata.org', 'wikidata-test-repo.wikimedia.de' ) ) ) {
	header( 'HTTP/1.1 404 Not Found' );
	echo 'The parameter repo is not valid.';
	exit();
}

$basePedia = new BasePedia();

if( $ids === array() ) {
	if( $sites === array() || $titles === array() || count( $titles ) > 50 ) {
		header( 'HTTP/1.1 404 Not Found' );
		echo 'No entities found.';
		exit();
	} else {
		$basePedia->addEntitiesFromSitelinks( $sites, $titles, $languages );
	}
} else {
	if( $sites === array() && $titles === array() && count( $ids ) <= 50 ) {
		$basePedia->addEntitiesFromIds( $ids, $languages );
	} else {
		header( 'HTTP/1.1 409 Conflict' );
		echo 'Either provide the item "id" or pairs of "site" and "title" for a corresponding page.';
		exit();
	}
}
$graph = $basePedia->getRdfGraph( $languages );

if( $formatId === 'html' ) {
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>BasePedia</title></head><body>';
	echo $graph->dump( true );
	echo '</body></html>';
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
