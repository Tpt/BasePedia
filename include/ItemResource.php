<?php

/**
 * Converts an item to RDF.
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
class ItemResource extends EntityResource {

	/**
	 * @see EntityResource::updateGraph
	 */
	public function updateGraph( $withDocument = true ) {
		parent::updateGraph( $withDocument );

		$this->resource->setType( 'wb:Item' );
		$this->resource->add( 'owl:sameAs', $this->graph->resource( BasePedia::getEntityUriInRepo( $this->data['id'] ) ) );

		foreach( $this->data['sitelinks'] as $link ) {
			$title = str_replace( ' ', '_', $link['title'] );
			if( preg_match( '/^(.*)wiki$/', $link['site'], $m ) ) {
				$this->resource->add( 'foaf:isPrimaryTopicOf', $this->graph->resource( 'http://' . str_replace( '_', '-', $m[1] ) . '.wikipedia.org/wiki/' . $title ) );
				if( $m[1] === 'en' ) {
					$this->resource->add( 'owl:sameAs', $this->graph->resource( 'http://dbpedia.org/resource/' . $this->dbPediaEncode( $title ) ) );
				} else if( in_array( $m[1], array( 'fr', 'it', 'ja', 'ko', 'cs', 'el', 'pl', 'ru', 'es', 'de', 'pt' ) ) ) {
					$this->resource->add( 'owl:sameAs', $this->graph->resource( 'http://' . $m[1] . '.dbpedia.org/resource/' . $this->dbPediaEncode( $title ) ) );
				}
			}
		}

		foreach( $this->data['claims'] as $list ) {
			foreach( $list as $claim ) {
				if( isset( $claim['mainsnak'] ) ) {
					$this->addSnak( $claim['mainsnak'] );					
				}
			}
		}
	}

	/**
	 * Add a snak to the RDF description
	 * @param array $snak the snak as outputed by the Wikidata API
	 */
	protected function addSnak( array $snak ) {
		$value = null;
		switch( $snak['snaktype'] ) {
			case 'value':
				$value = $this->getValueFromDataValue( $snak['property'], $snak['datavalue'] );
				break;
			case 'somevalue':
				$value = $this->graph->resource( 'wb:something' );
				break;
			case 'novalue':
				$value = $this->graph->resource( 'wb:nothing' );
				break;
		}
		if( $value !== null ) {
			$this->resource->add( $this->graph->resource( BasePedia::getEntityUri( $snak['property'] ) ), $value );
		}
	}

	/**
	 * Returns the RDF value of a DataValue
	 * @param string $propertyId the propertyId
	 * @param array $value the datavalue as outputed by the Wikidata API
	 * @return EasyRdf_Literal|EasyRdf_Resource|null
	 */
	protected function getValueFromDataValue( $propertyId, array $value ) {
		$type = BasePedia::singleton()->getPropertyDatatype( $propertyId );
		if( $type === null ) {
			$type = $value['type'];
		}

		switch( $type ) {
			case 'wikibase-item':
			case 'wikibase-entityid':
				switch( $value['value']['entity-type'] ) {
					case 'item':
						return $this->graph->resource( BasePedia::getEntityUri( 'q' . $value['value']['numeric-id'] ) );
					case 'property':
						return $this->graph->resource( BasePedia::getEntityUri( 'p' . $value['value']['numeric-id'] ) );
				}
				return null;
			case 'string':
				return new EasyRdf_Literal( $value['value'] );
			case 'commonsMedia':
				return $this->graph->resource( 'http://commons.wikimedia.org/wiki/File:' . $value['value'] );
		}
		return null;
	}

	/**
	 * @see EntityResource::cleanStructure
	 */
	protected function cleanStructure() {
		parent::cleanStructure();

		foreach( array( 'links' ) as $field ) {
			if( !isset( $this->data[$field] ) ) {
				$this->data[$field] = array();
			}
		}
	}

	/**
	 * Encode a MW title as dbPedia does
	 */
	protected function dbPediaEncode( $title ) {
		return str_replace(
			array( ' ', '"', '#', '%', '<', '>', '?', '[', '\\', ']', '^', '`', '{', '|', '}' ),
			array( '_', '%22', '%23', '%25', '%3C', '%3E', '%3F', '%5B', '%5C', '%5D', '%5E', '%60', '%7B', '%7C', '%7D' ),
			$title
		);
	}
}
