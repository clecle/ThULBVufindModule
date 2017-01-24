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
 * @category ThULB
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
namespace ThULB\RecordTab;
use  VuFind\RecordTab\CollectionList as OriginalCollectionList;

/**
 * Collection list tab
 *
 * @category ThULB
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
class ArticleCollectionList extends OriginalCollectionList
{

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'related_articles';
    }
    
    /**
     * Is this tab initially visible?
     *
     * @return bool
     */
    public function isVisible()
    {
        $visible = false;
        
        $marcLeader = $this->getRecordDriver()->getMarcRecord()->getLeader();
        
        /**
         * @see: http://www.loc.gov/marc/bibliographic/bdleader.html
         * 
         * Look for children, if ...
         * - "Multipart resource record level" is "Part with dependent title"
         * - "Bibliographic level" is "Monograph/Item"
         * - "Bibliographic level" is "Serial"
         */
        if ($marcLeader[18] === 'c'
            || in_array($marcLeader[6], array('m', 's'))
        ) {
            // VuFind\Search\SolrCollection\Params::initFromRecordDriver()
            // throws an Exception, if no collection ID could be found
            try {
                $result = $this->getResults();
            } catch (\Exception $e) {
                return false;
            }
            $visible = is_array($result->getResults()) && !empty($result->getResults());
        }
        
        return $visible;
    }
}
