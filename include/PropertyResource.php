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
		parent::updateGraph( true );
		$this->resource->setType( 'wb:Property' );

		switch( $this->data['datatype'] ) {
			case 'wikibase-item':
				$this->resource->add( 'rdfs:range', $this->graph->resource( 'wb:Item' ) );
				break;
			case 'commonsMedia':
			case 'string':
				$this->resource->add( 'rdfs:range', $this->graph->resource( 'rdfs:Literal' ) );
				break;
		}
	}

	/**
	 * @see EntityResource::cleanStructure
	 */
	protected function cleanStructure() {
		parent::cleanStructure();
	}
}
