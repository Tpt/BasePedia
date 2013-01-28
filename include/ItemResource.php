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
		parent::updateGraph( true );
		$this->resource->setType( 'wb:Item' );

		foreach( $this->data['sitelinks'] as $link ) {
			$title = str_replace( ' ', '_', $link['title'] );
			if( preg_match( '/^(.*)wiki$/', $link['site'], $m ) ) {
				$this->resource->add( 'foaf:isPrimaryTopicOf', $this->graph->resource( 'http://' . str_replace( '_', '-', $m[1] ) . '.wikipedia.org/wiki/' . $title ) );
				if( $m[1] === 'en' ) {
					$this->resource->add( 'owl:sameAs', $this->graph->resource( 'http://dbpedia.org/resource/' . $title ) );
				} else if( in_array( $m[1], array( 'fr', 'it', 'ja', 'ko', 'cs', 'el', 'pl', 'ru', 'es', 'de', 'pt' ) ) ) {
					$this->resource->add( 'owl:sameAs', $this->graph->resource( 'http://' . $m[1] . '.dbpedia.org/resource/' . $title ) );
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
		switch( $snak['snaktype'] ) {
			case 'value':
				$this->resource->add( $this->graph->resource( BasePedia::getEntityUri( $snak['property'] ) ), $this->getValueFromDataValue( $snak['datavalue'] ) );
				break;
		}
	}

	/**
	 * Returns the RDF value of a DataValue
	 * @param array $value the datavalue as outputed by the Wikidata API
	 * @return EasyRdf_Literal|EasyRdf_Resource|null
	 */
	protected function getValueFromDataValue( array $value ) {
		switch( $value['type'] ) {
			case 'wikibase-entityid':
				switch( $value['value']['entity-type'] ) {
					case 'item':
						return $this->graph->resource( BasePedia::getEntityUri( 'Q' . $value['value']['numeric-id'] ) );
					case 'property':
						return $this->graph->resource( BasePedia::getEntityUri( 'P' . $value['value']['numeric-id'] ) );
				}
				break;
			case 'string':
				return new EasyRdf_Literal( $value['value'] );
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
}
