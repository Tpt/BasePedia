<?php

/**
 * Mapping from Wikidata identifiers to vocabularies
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

//WikidataProperty rdfs:subPropertyOf XXXX
$wgBasePediaMappingProperties = array(
	'p31' => 'rdf:instanceOf',
	'p279' => 'rdfs:subClassOf',
	'p107' => 'rdf:instanceOf',
	'p18' => 'foaf:depiction',
	'p21' => 'foaf:gender',
	'p275' => 'dc:license',
	'p407' => 'dc:language',
	'p170' => 'dc:creator',
	'p50' => 'dc:creator',
	'p123' => 'dc:publisher'
);

