<?php
/**
 * Collection list tab
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
namespace ThULB\RecordTab;
use VuFind\RecordTab\CollectionList as OriginalCollectionList,
        VuFind\Search\RecommendListener;

/**
 * Collection list tab
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
class CollectionList extends OriginalCollectionList
{
    protected $isArticlesTab;
    
    /**
     * Is this tab initially visible?
     *
     * @return bool
     */
    public function isVisible()
    {
        $visible = false;
        
        try {
            $visible = $this->getRecordDriver()->isCollection() || $this->isArticlesTab();
        } catch (\Exception $ex) {
             $visible = false;
        }
        
        return $visible;
    }
    
    protected function isArticlesTab()
    {
        if (is_null($this->isArticlesTab)) {
            $this->isArticlesTab = false;
            
            $marcLeader = $this->getRecordDriver()->getMarcRecord()->getLeader();

            /**
             * @see: http://www.loc.gov/marc/bibliographic/bdleader.html
             * 
             * Look for children, if at least one of this is the case:
             * - "Multipart resource record level" is "Part with dependent title"
             * - "Bibliographic level" is "Monograph/Item"
             * - "Bibliographic level" is "Serial"
             */
            if (in_array($marcLeader[19], array('c', ' '))
                || in_array($marcLeader[7], array('m', 's'))
            ) {
                // VuFind\Search\SolrCollection\Params::initFromRecordDriver()
                // throws an Exception, if no collection ID could be found
                try {
                    $result = $this->getResults();
                } catch (\Exception $e) {
                    return false;
                }
                $this->isArticlesTab = is_array($result->getResults())
                                           && !empty($result->getResults());
            }
        }
        
        return $this->isArticlesTab;
    }
}
