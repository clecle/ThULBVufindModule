<?php
/**
 * Article Collection list tab
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
use VuFind\RecordTab\CollectionList as OriginalCollectionList,
        VuFind\Search\RecommendListener;

/**
 * Article Collection list tab
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
     * Get the processed search results.
     *
     * @return \VuFind\Search\SolrCollection\Results
     */
    public function getResults()
    {
        if (null === $this->results) {
            $driver = $this->getRecordDriver();
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray();
            $rManager = $this->recommendManager;
            $cb = function ($runner, $params, $searchId) use ($driver, $rManager) {
                $params->initFromRecordDriver($driver);
                $params->addHiddenFilter('format:Article');
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };
            $this->results
                = $this->runner->run($request, 'SolrCollection', $cb);
        }
        return $this->results;
    }
}
