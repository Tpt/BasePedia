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
	const LICENCE_URI = 'http://creativecommons.org/publicdomain/zero/1.0/';
	const BASE_URI = 'http://basepedia.wmflabs.org';
	const BASE_REPO_URI = 'http://www.wikidata.org';

	protected function __construct() {
		$this->http = new Http( 'BasePedia/0.1 by User:Tpt' );
	}

	/**
	 * @return EasyRdf_Graph
	 */
	public function getRdfGraph( array $languages ) {
		$graph = $this->createBaseGraph();
		$propertiesUsed = array();
		$mainEntitiesId = array();

		//GetUsedProperties
		foreach( $this->entities as $id => $entity ) {
			$mainEntitiesId[] = $id;
			if( isset( $entity['claims'] ) ) {
				foreach( $entity['claims'] as $prop => $claim ) {
					if( !isset( $this->entities[$prop] ) ) {
						$propertiesUsed[$prop] = strtoupper( $prop );
					}
				}
			}
		}

		//Load properties
		$this->addEntitiesFromIds( $propertiesUsed, $languages );

		//Add "main" entities
		foreach( $mainEntitiesId as $id ) {
			$this->addEntityToGraph( $graph, $this->entities[$id], true );
			unset( $this->entities[$id] );
		}

		//Add remaining properties
		foreach( $this->entities as $id => $entity ) {
			$this->addEntityToGraph( $graph, $entity, false );
		}

		return $graph;
	}

	/**
	 * @return EasyRdf_Graph
	 */
	protected function createBaseGraph() {
		EasyRdf_Namespace::set( 'wd', self::BASE_URI . '/id/' );
		EasyRdf_Namespace::set( 'wb', self::BASE_URI . '/ontology#' );
		return new EasyRdf_Graph();
	}

	protected function addEntityToGraph( EasyRdf_Graph $graph, array $entity, $withDocument ) {
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
		$url = self::BASE_REPO_URI . '/w/api.php?' . http_build_query( $params + array( 'format' => 'php' ) );
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
		return self::BASE_REPO_URI . '/wiki/' . strtoupper( $id );
	}

	/**
	 * Returns URI of a Wikibase entity
	 * @param string $id The id of the entity like "q23"
	 */
	public static function getEntityUri( $id ) {
		return self::BASE_URI . '/id/' . strtolower( $id );
	}

	/**
	 * Returns URI of a Wikibase entity in the repository
	 * @param string $id The id of the entity like "q23"
	 */
	public static function getEntityUriInRepo( $id ) {
		return self::BASE_REPO_URI . '/id/' . strtolower( $id );
	}

	/**
	 * Return the datatype for an already loade property
	 * @param string $propertyId propertyId
	 * @return string|null
	 */
	public function getPropertyDatatype( $propertyId ) {
		$propertyId = strtolower( $propertyId );
		if( !isset( $this->entities[$propertyId] ) || !isset( $this->entities[$propertyId]['datatype'] ) ) {
			return null;
		}
		return $this->entities[$propertyId]['datatype'];
	}

	/**
	 * @return BasePedia
	 */
	public static function singleton() {
		static $self;
		if( $self === null ) {
			$self = new BasePedia();
		}
		return $self;
	}
}
