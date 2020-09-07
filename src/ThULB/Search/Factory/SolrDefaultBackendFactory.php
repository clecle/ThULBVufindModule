<?php

namespace ThULB\Search\Factory;

use ThULB\Backend\Solr\Backend;
use VuFind\Search\Factory\SolrDefaultBackendFactory as OriginalFactory;
use VuFindSearch\Backend\Solr\LuceneSyntaxHelper;
use ThULB\Backend\Solr\QueryBuilder;

class SolrDefaultBackendFactory extends OriginalFactory
{
    /**
     * Solr backend class
     *
     * @var string
     */
    protected $backendClass = Backend::class;

    /**
     * Create the query builder.
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        $specs = $this->loadSpecs();
        $config = $this->config->get($this->mainConfig);
        $defaultDismax = isset($config->Index->default_dismax_handler)
            ? $config->Index->default_dismax_handler : 'dismax';
        // Use ThULB QueryBuilder
        $builder = new QueryBuilder($specs, $defaultDismax);

        // Configure builder:
        $search = $this->config->get($this->searchConfig);
        $caseSensitiveBooleans
            = isset($search->General->case_sensitive_bools)
            ? $search->General->case_sensitive_bools : true;
        $caseSensitiveRanges
            = isset($search->General->case_sensitive_ranges)
            ? $search->General->case_sensitive_ranges : true;
        $helper = new LuceneSyntaxHelper(
            $caseSensitiveBooleans, $caseSensitiveRanges
        );
        $builder->setLuceneHelper($helper);

        return $builder;
    }
}