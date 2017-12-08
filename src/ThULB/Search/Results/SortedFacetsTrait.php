<?php

namespace ThULB\Search\Results;

/**
 * A trait to override the function to get a facet list with a version that
 * addiotinally sorts the facets
 */
trait SortedFacetsTrait
{
    /**
     * Returns the stored list of facets for the last search with all applied
     * facet fields first.
     *
     * @param array $filter Array of field => on-screen description listing
     * all of the desired facet fields; set to null to get all configured values.
     *
     * @return array        Facets data arrays
     */
    public function getFacetList($filter = null)
    {
        $facetList = parent::getFacetList($filter);
        
        $sort = function ($facetFieldA, $facetFieldB) {
            if ($facetFieldA['isApplied'] === $facetFieldB['isApplied']) {
                return ($facetFieldA['count'] > $facetFieldB['count']) ? -1 : 1;
            }
            
            return ($facetFieldA['isApplied']) ? -1 : 1;
        };
        
        foreach ($facetList as $facetLabel => $facetData) {
            usort($facetData['list'], $sort);
            $facetData['counts'] = array_values($facetData['list']);
            $facetData['list'] = $facetData['counts'];
            $facetList[$facetLabel] = $facetData;
        }
        
        return $facetList;
    }
}
