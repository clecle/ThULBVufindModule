<?php
/**
 * Factory for record driver data formatting view helper
 *
 * PHP version 5
 * 
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
namespace ThULB\View\Helper\Root;

use VuFind\View\Helper\Root\RecordDataFormatter;
use VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder;
use VuFind\View\Helper\Root\RecordDataFormatterFactory as OrignalFactory;

/**
 * Factory for record driver data formatting view helper
 *
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class RecordDataFormatterFactory extends OrignalFactory
{
    /**
     * Create the helper.
     *
     * @return RecordDataFormatter
     */
    public function __invoke()
    {
        $helper = parent::__invoke();
        $helper->setDefaults('full', $this->getDefaultFullSpecs());
        
        return $helper;
    }
    
    /**
     * Get default specifications for displaying data in full metadata.
     *
     * @return array
     */
    public function getDefaultFullSpecs()
    {
        $spec = new SpecBuilder();
        $spec->setLine('PartInfo', 'getPartInfo');
        $spec->setTemplateLine(
            'Published in', 'getContainerTitle', 'data-containerTitle.phtml'
        );
        $spec->setLine(
            'New Title', 'getNewerTitles', null, ['recordLink' => 'title']
        );
        $spec->setLine(
            'Previous Title', 'getPreviousTitles', null, ['recordLink' => 'title']
        );
        $spec->setTemplateLine(
            'Main Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['main']) > 1
                        ? 'Main Authors' : 'Main Author';
                },
                'context' => ['type' => 'main', 'schemaLabel' => 'author'],
            ]
        );
        $spec->setTemplateLine(
            'Corporate Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['corporate']) > 1
                        ? 'Corporate Authors' : 'Corporate Author';
                },
                'context' => ['type' => 'corporate', 'schemaLabel' => 'creator'],
            ]
        );
        $spec->setTemplateLine(
            'Other Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'context' => [
                    'type' => 'secondary', 'schemaLabel' => 'contributor'
                ],
            ]
        );
        $spec->setLine(
            'Format', 'getFormats', 'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setTemplateLine('Languages', 'getLanguages', 'data-languages.phtml');
        $spec->setTemplateLine('LanguageNotes', true, 'data-language_notes.phtml');
        $spec->setTemplateLine(
            'Publication Metadata', 'getPublicationDetails', 'data-publicationDetails.phtml'
        );
        $spec->setLine('Map Scale', 'getCartographicScale');
        $spec->setLine('Map Projection', 'getCartographicProjection');
        $spec->setLine('Map Coordinates', 'getCartographicCoordinates');
        $spec->setLine('Map Equinox', 'getCartographicEquinox');
        $spec->setLine(
            'Edition', 'getEdition', null,
            ['prefix' => '<span property="bookEdition">', 'suffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setTemplateLine('Numbering', true, 'data-numbering.phtml');
        $spec->setTemplateLine('NumPecs', true, 'data-numbering_peculiarities.phtml');
        $spec->setTemplateLine(
            'Subjects', 'getAllSubjectHeadings', 'data-allSubjectHeadings.phtml'
        );
        $spec->setTemplateLine(
            'child_records', 'getChildRecordCount', 'data-childRecords.phtml',
            ['allowZero' => false]
        );
        $spec->setTemplateLine('Online Access', true, 'data-onlineAccess.phtml');
        $spec->setTemplateLine(
            'Related Items', 'getAllRecordLinks', 'data-allRecordLinks.phtml'
        );
        $spec->setTemplateLine('Tags', true, 'data-tags.phtml');
        $spec->setLine('Item Description', 'getGeneralNotes');
        $spec->setLine('Physical Description', 'getPhysicalDescriptions');
        $spec->setTemplateLine('Fingerprint', 'getFingerprint', 'data-fingerprint.phtml');
        $spec->setLine('Bibliographic Citations', 'getBibliographicCitation');
        $spec->setLine('Publication Frequency', 'getPublicationFrequency');
        $spec->setLine('Playing Time', 'getPlayingTimes');
        $spec->setLine('Audience', 'getTargetAudienceNotes');
        $spec->setLine('Awards', 'getAwards');
        $spec->setLine('Production Credits', 'getProductionCredits');
        $spec->setLine('Bibliography', 'getBibliographyNotes');
        $spec->setLine('ISBN', 'getISBNs');
        $spec->setLine('ISSN', 'getISSNs');
        /* ZDB Id */
        $spec->setTemplateLine('ZDB', true, 'data-zdb.phtml');
        $spec->setLine('DOI', 'getCleanDOI');
        $spec->setLine('Access', 'getAccessRestrictions');
        $spec->setLine('Finding Aid', 'getFindingAids');
        $spec->setLine('Publication_Place', 'getHierarchicalPlaceNames');
        $spec->setTemplateLine('Author Notes', true, 'data-authorNotes.phtml');
        $spec->setTemplateLine('Basic Classification', true, 'data-basicClassification.phtml');
        $spec->setTemplateLine('ThuBiblio Classification', true, 'data-thuBiblioClassification.phtml');
        return $spec->getArray();
    }
}
