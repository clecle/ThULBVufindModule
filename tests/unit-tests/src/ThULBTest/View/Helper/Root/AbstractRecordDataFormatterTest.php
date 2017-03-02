<?php

namespace ThULBTest\View\Helper\Root;

use VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use ThULB\View\Helper\Root\RecordDataFormatterFactory;
use ThULBTest\View\Helper\AbstractViewHelperTest;

/**
 * Generalized testing class for the record data formatter view helper. It makes
 * it easy, to add new tests by simple inheritance.
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
abstract class AbstractRecordDataFormatterTest extends AbstractViewHelperTest
{   
    const USED_FIELDS_MARKER = 'Genutzte Felder';

    /**
     * Provides the name of the sheet of rda.xlsx, that holds the test cases
     * 
     * @var string
     */
    protected $sheetName;
    
    /**
     * Optional name of the template, that is used by the view helper. This
     * variable needs to be provided, if $recordDriverFunction is not used.
     * 
     * @var string 
     */
    protected $template;
    
    /**
     * Optional name of the function of the record driver, that provides the
     * data for the view helper. This variable needs to be provided, if 
     * $template is not used.
     *
     * @var string 
     */
    protected $recordDriverFunction;
    
    /**
     * Optional key for the meta data that is tested, like it is used in  the
     * translation ini files. This might be necessary later on, if translation
     * should be tested too.
     * 
     * @var string 
     */
    protected $metadataKey;
    
    /**
     * Setup test case.
     *
     * Mark test skipped if short_open_tag is not enabled. The partial
     * uses short open tags. This directive is PHP_INI_PERDIR,
     * i.e. can only be changed via php.ini or a per-directory
     * equivalent. The test will fail if the test is run on
     * a system with short_open_tag disabled in the system-wide php
     * ini-file.
     *
     * @return void
     */
    protected function setup()
    {
        parent::setup();
        if (!ini_get('short_open_tag')) {
            $this->markTestSkipped('Test requires short_open_tag to be enabled');
        }
    }
    
    /**
     * Main testing function. In normal cases, it is enough, to provide either
     * a template path that has to be used by the helper or a record driver
     * function in the derived class variables $template and $recordDriverFunction.
     * If both are needed for the view helper, provide both.
     * 
     * In more complex theoretical cases overwrite this function or use other
     * functions "test[...]()" besides this one.
     */
    public function testFormatting()
    {
        $key = is_null($this->metadataKey) ? 'test' : $this->metadataKey;
        
        foreach ($this->getRelevantRows() as list($comment, $ppn, $longView, $shortView, $link)) {
            $record = $this->getRecordFromFindex($ppn);
            $formatter = $this->getFormatter();

            $spec = $this->getFormatterSpecBuilder();
            if (!is_null($this->template)) {
                $spec->setTemplateLine(
                        $key,
                        is_null($this->recordDriverFunction) ? true : $this->recordDriverFunction,
                        $this->template
                    );
            } else if (!is_null($this->recordDriverFunction)) {
                $spec->setLine($this->metadataKey, $this->recordDriverFunction);
            } else {
                $this->markTestSkipped('No information about template or record driver function provided in class  ' . get_class($this));
            }

            $data = $formatter->getData($record, $spec->getArray());

            $this->assertContains($longView, $data[$key]);
        }
    }
    
    /**
     * Extracts the relevant rows from the test cases spreadsheet.
     * 
     * @return array
     */
    protected function getRelevantRows()
    {
        $relevantRows = [];
        
        /** @var \Box\Spout\Writer\Common\Sheet $sheet */
        foreach ($this->getSpreadSheetReader()->getSheetIterator() as $sheet) {
            if ($sheet->getName() === $this->sheetName) {
                $isRelevantRow = false;
                /** @var array $row */
                foreach ($sheet->getRowIterator() as $row) {
                    if (strpos($row[0], self::USED_FIELDS_MARKER) !== false) {
                        $isRelevantRow = true;
                        continue;
                    }
                    if ($isRelevantRow) {
                        if (empty($row[0])) {
                            break;
                        }
                        $relevantRows[] = array_slice($row, 0, 5);
                    }
                }
                break;
            }
        }
        if (empty($relevantRows)) {
            $this->markTestSkipped('No sheet found for ' . get_class($this) . 
                    '. Add it to rda.xlsx and define it in the test class.');
        }
        
        return $relevantRows;
    }
    
    protected function getSpreadSheetReader()
    {
        $spreadsheetReader = ReaderFactory::create(Type::XLSX);
        $spreadsheetReader->open(PHPUNIT_FIXTURES_THULB . '/spreadsheet/rda.xlsx');
        
        return $spreadsheetReader;
    }
    
    /**
     * Build a formatter, including necessary mock view w/ helpers.
     *
     * @return RecordDataFormatter
     */
    protected function getFormatter()
    {
        // Build the formatter:
        $factory = new RecordDataFormatterFactory();
        $formatter = $factory->__invoke();

        // Create a view object with a set of helpers:
        $helpers = $this->getViewHelpers();
        $view = $this->getPhpRenderer($helpers);

        // Mock out the router to avoid errors:
        $match = new \Zend\Mvc\Router\RouteMatch([]);
        $match->setMatchedRouteName('foo');
        $view->plugin('url')
            ->setRouter($this->getMock('Zend\Mvc\Router\RouteStackInterface'))
            ->setRouteMatch($match);

        // Inject the view object into all of the helpers:
        $formatter->setView($view);
        foreach ($helpers as $helper) {
            $helper->setView($view);
        }

        return $formatter;
    }
    
    protected function getFormatterSpecBuilder()
    {
        return new SpecBuilder();
    }
}
