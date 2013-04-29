<?php

/**
 * Base class to convert entities to RDF.
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
abstract class EntityResource {

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var EasyRdf_Graph
	 */
	protected $graph;

	/**
	 * @var EasyRdf_Resource
	 */
	protected $resource;

	/**
	 * @param array $data Data from the API
	 * @param EasyRdf_Graph $this->graph
	 */
	public function __construct( array $data, EasyRdf_Graph $graph ) {
		$this->data = $data;
		$this->graph = $graph;
		$this->cleanStructure();
	}

	/**
	 * Add to the graph the entity
	 * @param boolean $withDocument include the description of the page
	 */
	public function updateGraph( $withDocument ) {
		if( $withDocument ) {
			$page = $this->graph->resource( BasePedia::getDocumentUri( $this->data['id'] ), 'foaf:Document' );
			$page->addType( array( 'cc:Work' ) );
			$page->add( 'dc:modified', new EasyRdf_Literal_Date( $this->data['modified'] ) );
			$page->add( 'cc:license', $this->graph->resource( BasePedia::LICENCE_URI ) );
		}

		$this->resource = $this->graph->resource( BasePedia::getEntityUri( $this->data['id'] ), 'rdfs:Resource' );
		$this->resource->add( 'foaf:isPrimaryTopicOf', $this->graph->resource( BasePedia::getDocumentUri( $this->data['id'] ) ) );
		if( $withDocument ) {
			$page->add( 'foaf:primaryTopic', $this->resource );
		}

		foreach( $this->data['labels'] as $label ) {
			$this->resource->add( 'skos:prefLabel', new EasyRdf_Literal( $label['value'], $label['language'] ) );
		}
		foreach( $this->data['descriptions'] as $description ) {
			$this->resource->add( 'skos:note', new EasyRdf_Literal( $description['value'], $description['language'] ) );
		}
		foreach( $this->data['aliases'] as $aliases ) {
			foreach( $aliases as $alias ) {
				$this->resource->add( 'skos:altLabel', new EasyRdf_Literal( $alias['value'], $alias['language'] ) );
			}
		}
	}

	/**
	 * Cleans the internal array structure.
	 */
	protected function cleanStructure() {
		foreach( array( 'labels', 'descriptions', 'aliases', 'claims' ) as $field ) {
			if( !isset( $this->data[$field] ) ) {
				$this->data[$field] = array();
			}
		}
	}

}
