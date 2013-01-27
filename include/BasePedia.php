<?php

/**
 * Base class of the software.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @licence GNU GPL v2+
 * @author Thomas Pellissier Tanon
 */
class BasePedia {
	protected $http;
	protected $entities = array();
	const HOST = 'wikidata-test-repo.wikimedia.de'; //'wikidata.org';
	const LICENCE_URI = 'http://creativecommons.org/publicdomain/zero/1.0/';

	public function __construct() {
		$this->http = new Http( 'BasePedia/0.1 by User:Tpt' );
	}

	/**
	 * @return EasyRdf_Graph
	 */
	public function getRdfGraph( array $languages ) {
		$graph = $this->createBaseGraph();
		$propertiesUsed = array();
		foreach( $this->entities as $id => $entity ) {
			//Get used properties
			if( isset( $entity['claims'] ) ) {
				foreach( $entity['claims'] as $prop => $claim ) {
					if( !isset( $this->entities[$prop] ) ) {
						$propertiesUsed[$prop] = strtoupper( $prop );
					}
				}
			}

			$this->addEntityToGraph( $graph, $entity, true );
			unset( $this->entities[$id] );
		}

		$this->addEntitiesFromIds( $propertiesUsed, $languages );
		foreach( $this->entities as $id => $entity ) {
			$this->addEntityToGraph( $graph, $entity, false );
		}

		return $graph;
	}

	/**
	 * @return EasyRdf_Graph
	 */
	protected function createBaseGraph() {
		EasyRdf_Namespace::set( 'wd', 'http://' . self::HOST . '/id/' );
		EasyRdf_Namespace::set( 'wb', 'http://wikidata.org/schema/' );
		$graph = new EasyRdf_Graph();

		$item = $graph->resource( 'wb:Item' );
		$item->setType( 'rdfs:Class' );
		$item->add( 'rdfs:subClassOf', $graph->resource( 'rdfs:Resource' ) );

		$property = $graph->resource( 'wb:Property' );
		$property->setType( 'rdfs:Class' );
		$property->add( 'rdfs:subClassOf', $graph->resource( 'rdf:Property' ) );
		return $graph;
	}

	protected function addEntityToGraph( EasyRdf_Graph $graph, array $entity, $withDocument = true ) {
		switch( $entity['type'] ) {
			case 'item':
				$item = new ItemResource( $entity, $graph );
				$item->updateGraph( $withDocument );
				break;
			case 'property':
				$prop = new PropertyResource( $entity, $graph );
				$prop->updateGraph( $withDocument );
				break;
		}
	}

	/**
	 * @param string[] $ids entities ids to get
	 * @throws Exception
	 */
	public function addEntitiesFromIds( array $ids, array $languages = array() ) {
		$params = array(
			'action' => 'wbgetentities',
			'ids' => implode( $ids, '|' )
		);
		if( $languages !== array() ) {
			$params['languages'] = implode( $languages, '|' );
		}
		$response = $this->get( $params );
		$this->parseGetEntitiesApiResponse( $response );
	}

	/**
	 * @param string[] $sites entities sites to get
	 * @param string[] $titles entities titles to get
	 * @throws Exception
	 */
	public function addEntitiesFromSitelinks( array $sites, array $titles, array $languages = array() ) {
		$params = array(
			'action' => 'wbgetentities',
			'sites' => implode( $sites, '|' ),
			'titles' => implode( $titles, '|' )
		);
		if( $languages !== array() ) {
			$params['languages'] = implode( $languages, '|' );
		}
		$response = $this->get( $params );
		$this->parseGetEntitiesApiResponse( $response );
	}

	/**
	 * @param string[] $params parameter to put in the url
	 * @return array the API result
	 * @throws Exception
	 */
	protected function get( array $params ) {
		$data = 'format=php';
		foreach( $params as $name => $value ) {
			$data .= '&' . $name . '=' . rawurlencode( $value );
		}
		$url = 'http://' . self::HOST . '/w/api.php?' . $data;
		$response = $this->http->get( $url );
		return unserialize( $response );
	}

	/**
	 * @param array $result the wbgetentities api response
	 */
	protected function parseGetEntitiesApiResponse( array $result ) {
		if( isset( $result['entities'] ) ) {
			foreach( $result['entities'] as $data ) {
				if( !isset( $data['missing'] ) ) {
					$this->entities[$data['id']] = $data;
				}
			}
		}
	}

	/**
	 * Returns URI of a Wikidata page
	 * @param string $id The id of the entity like "q23"
	 */
	public static function getDocumentUri( $id ) {
		return 'http://' . self::HOST . '/wiki/' . strtoupper( $id );
	}

	/**
	 * Returns URI of a Wikidata entity
	 * @param string $id The id of the entity like "q23"
	 */
	public static function getEntityUri( $id ) {
		return 'http://' . self::HOST . '/id/' . strtoupper( $id );
	}
}
