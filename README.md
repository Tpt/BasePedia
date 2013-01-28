BasePedia is a draft of RDF output of the [Wikidata](http://wikidata.org/) API using widely used vocabularies.

BasePedia is currently in active development and does not provide a stable URI system. BasePedia support phase 1 data and a beginning of a very simplified output of Phase 2 statements.

BasePedia is by default configure to use [wikidata.org](http://wikidata.org). If you want to test phase 2 features, use [Wikimedia DE test repository](http://wikidata-test-repo.wikimedia.de) by adding &repo=wikidata-test-repo.wikimedia.de to the URLs.

The entry point, index.php, currently support most of parameters of the wbgetentities MediaWiki api.

These parameters are supported:

* ids: The IDs of the entities to get the data from
		Separate values with '|'
		Maximum number of values: 50

* sites: Identifier for the site on which the corresponding page resides
		Use together with 'title', but only give one site for several titles or several sites for one title.
		Values (separate with '|') like "enwiki' or "zh_classicalwiki"
		Maximum number of values: 50

* titles The title of the corresponding page
		Use together with 'sites', but only give one site for several titles or several sites for one title.
		Separate values with '|'
		Maximum number of values: 50

* languages: By default the internationalized values are returned in all available languages.
		This parameter allows filtering these down to one or more languages by providing one or more language codes.
		Values (separate with '|') like "en" or kk-cyrl"

* format: Output format to use
		By default html
		Values: html, rdfxml, json, turtle, n3, png, svg

Examples:
* Get item with ID q42 with language attributes in all available languages in RDF/XML: index.php?format=rdfxml&ids=q42
* Get item with ID q42 with language attributes in English language and in HTML: index.php?format=html&ids=q42&languages=en
* Get the item for page "Berlin" on the site "enwiki", with language attributes in English language and in Turtle: index.php?format=turtle&sites=enwiki&titles=Berlin&languages=en
