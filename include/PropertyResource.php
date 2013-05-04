<?php

/**
 * Convert a property to RDF.
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
class PropertyResource extends EntityResource {

	/**
	 * @see EntityResource::updateGraph
	 */
	public function updateGraph( $withDocument = true ) {
		global $wgBasePediaMappingProperties;

		parent::updateGraph( $withDocument );

		$this->resource->setType( 'wb:Property' );

		$range = $this->getRange( $this->data['datatype'] );
		if( $range !== null ) {
			$this->resource->add( 'rdfs:range', $range );
		}

		if( isset( $wgBasePediaMappingProperties[$this->data['id']] ) ) {
			$this->resource->add( 'rdfs:subPropertyOf', $this->graph->resource( $wgBasePediaMappingProperties[$this->data['id']] ) );
		}
	}

	/**
	 * @param string $datatype the datatype id
	 * @return EasyRdf_Resource|null the range of the property
	 */
	protected function getRange( $datatype ) {
		switch( $datatype ) {
			case 'wikibase-item':
				return $this->graph->resource( 'wb:Item' );
			case 'commonsMedia':
				return $this->graph->resource( 'foaf:Image' );
			case 'string':
				return $this->graph->resource( 'rdfs:Literal' );
		}
	}

	/**
	 * @see EntityResource::cleanStructure
	 */
	protected function cleanStructure() {
		parent::cleanStructure();
	}
}
