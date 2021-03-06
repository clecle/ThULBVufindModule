<?php

namespace ThULB\PDF;

use tFPDF;
use VuFind\View\Helper\Root\Translate;

class JournalRequest extends tFPDF
{
    // All Dimensions in 'mm'
    protected $dinA4width = 210;
    protected $dinA4height = 297;
    protected $printBorder = 5;
    protected $widthCallNumberCard = 50;
    protected $heightCallNumberCard = 105;
    protected $heightUserCard = 150;
    protected $widthBookCard = 50;

    // Form data
    protected $callNumber;
    protected $comment;
    protected $issue;
    protected $firstname;
    protected $lastname;
    protected $requestPages;
    protected $title;
    protected $username;
    protected $volume;
    protected $year;
    protected $orderedAt;

    // Description keys
    protected $descCallNumber = 'Call Number';
    protected $descComment = 'Note';
    protected $descIssue = 'Issue';
    protected $descName = 'Name';
    protected $descPages = 'storage_retrieval_request_page(s)';
    protected $descTitle = 'Title';
    protected $descVolume = 'storage_retrieval_request_volume';
    protected $descYear = 'storage_retrieval_request_year';
    protected $descUserNumber = "Benutzernr.";
    protected $descOrderedAt = "bestellt am";

    protected const DEFAULT_FONT_SIZE = 10;

    protected $translator;

    /**
     * Constructor.
     *
     * @param Translate $translator Translator to use.
     * @param string $orientation Page orientation.
     * @param string $unit Unit to measure pages.
     * @param string $size Size of the pages.
     */
    public function __construct(Translate $translator, $orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);

        $this->translator = $translator;

        $this->orderedAt = date('d.m.Y');
    }

    /**
     * Create the request pdf. Data must be set beforehand.
     */
    public function create() {
        $globalLocale = $this->translator->getTranslator()->getLocale();
        $this->translator->getTranslator()->addTranslationFile('ExtendedIni', null, 'default', 'de');
        $this->translator->getTranslator()->setLocale('de');

        $this->AddPage();
        $this->AddFont('DejaVu', '',  'DejaVuSansCondensed.ttf',true);
        $this->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf',true);
        $this->SetFont('DejaVu', '',  self::DEFAULT_FONT_SIZE);

        $this->SetMargins($this->printBorder, $this->printBorder);
        $this->SetAutoPageBreak(true, 0);

        $this->addLines();
        $this->addCardBook();
        $this->addCardUser();

        $this->addCardCallNumber();

        $this->translator->getTranslator()->setLocale($globalLocale);
    }

    /**
     * Add vertical and horizontal separation lines to the pdf.
     */
    protected function addLines() {
        $this->SetDrawColor(180);
        // card for books
        $this->Line(
            $this->widthBookCard, 0,
            $this->widthBookCard, $this->dinA4height
        );

        // card for users
        $this->Line(
            $this->widthBookCard, $this->heightUserCard,
            $this->dinA4width, $this->heightUserCard
        );

        // card for callnumbers
        $this->stripedLine(
            $this->dinA4width - $this->widthCallNumberCard, $this->heightCallNumberCard,
            $this->dinA4width, $this->heightCallNumberCard
        );
        $this->Line(
            $this->dinA4width - $this->widthCallNumberCard, 0,
            $this->dinA4width - $this->widthCallNumberCard, $this->heightUserCard
        );
        $this->SetDrawColor(0);
    }

    /**
     * Draw a dotted line.
     *
     * @param int $x1     X coordinate of the start point.
     * @param int $y1     Y coordinate of the start point.
     * @param int $x2     X coordinate of the end point.
     * @param int $y2     Y coordinate of the end point.
     * @param int $dashes Amount of dashes in this line, affects width of dashes
     */
    protected function stripedLine($x1, $y1, $x2, $y2, $dashes = 10) {
        $segmentWidth = ($x2 - $x1) / ($dashes * 2 - 1);
        for($segment = 0; $segment < ($dashes * 2 - 1); $segment++) {
            if($segment % 2) {
                continue;
            }

            $segmentOffset = $segment * $segmentWidth;
            $this->Line(
                $x1 + $segmentOffset, $y1,
                $x1 + $segmentOffset + $segmentWidth, $y2
            );
        }
    }

    /**
     * Add a text formatted as headline.
     * Sets XY coordinates for the next lines to be added.
     *
     * @param string $headline Headline text
     * @param int $x X coordinate of the headline.
     * @param int $y Y coordinate of the headline.
     * @param int $width Width of the headline.
     * @param int $spaceAtBottom Space between headline and the next text.
     */
    protected function addHeadLine($headline, $x, $y, $width, $spaceAtBottom = 0) {
        $this->SetXY($x, $y);
        $this->SetFont($this->FontFamily, 'UB', $this->FontSizePt + 3);
        $this->MultiCell($width, $this->FontSize, $headline);
        $this->SetFont($this->FontFamily, '', $this->FontSizePt - 3);
        $this->SetXY($x, $y + $spaceAtBottom);
    }

    /**
     * Write information for the user card to the pdf.
     */
    protected function addCardUser() {
        $availableTextWidth =
            $this->dinA4width - $this->widthCallNumberCard - $this->widthCallNumberCard - $this->printBorder * 2;

        $title = $this->shortenTextForWidth($this->title, $availableTextWidth - 25);
        $name = $this->lastname . ', ' . $this->firstname;

        $x = $this->widthCallNumberCard + $this->printBorder;
        $this->addHeadLine('Begleitzettel (freie Bestellbarkeit)', $x, $this->printBorder, $availableTextWidth, 16);

        $this->addText($this->descName,       $name,               $availableTextWidth, true);
        $this->addText($this->descUserNumber, $this->username,     $availableTextWidth, true);
        $this->addText($this->descOrderedAt,  $this->orderedAt,    $availableTextWidth, true);

        $this->SetXY($x, $this->GetY() + 10);

        $this->addText($this->descCallNumber, $this->callNumber,   $availableTextWidth, true, 'B');
        $this->addText($this->descTitle,      $title,              $availableTextWidth, true);
        $this->addText($this->descYear,       $this->year,         $availableTextWidth, true);
        $this->addText($this->descVolume,     $this->volume,       $availableTextWidth, true);
        $this->addText($this->descIssue,      $this->issue,        $availableTextWidth, true);
        $this->addText($this->descPages,      $this->requestPages, $availableTextWidth, true);
        $this->addText($this->descComment,    $this->comment,      $availableTextWidth, true);
    }

    /**
     * Write information for the callnumber card to the pdf.
     */
    protected function addCardCallNumber() {
        $availableTextWidth = $this->widthCallNumberCard - $this->printBorder * 2;

        $title = $this->shortenTextForWidth($this->title, $availableTextWidth, 2);
        $name = $this->lastname . ', ' . $this->firstname;

        $x = $this->dinA4width - $this->widthCallNumberCard + $this->printBorder;
        $y = $this->printBorder;
        $this->SetXY($x, $y);

        $this->addText("Leihfrist",           null,                $availableTextWidth);
        $this->SetXY($this->GetX(), $this->GetY() + 5);
        $this->addText($this->descCallNumber, $this->callNumber,   $availableTextWidth,
                      false, 'B', self::DEFAULT_FONT_SIZE + 2);
        $this->addText($this->descName,       $name,               $availableTextWidth);
        $this->addText($this->descUserNumber, $this->username,     $availableTextWidth);
        $this->addText($this->descOrderedAt,  $this->orderedAt,    $availableTextWidth);
        $this->addText($this->descTitle,      $title,              $availableTextWidth);
        $this->addText($this->descYear,       $this->year,         $availableTextWidth);
        $this->addText($this->descVolume,     $this->volume,       $availableTextWidth);
        $this->addText($this->descIssue,      $this->issue,        $availableTextWidth);
    }

    /**
     * Write information for the book card to the pdf.
     */
    protected function addCardBook() {
        $availableTextWidth = $this->widthCallNumberCard - $this->printBorder * 2;

        $title = $this->shortenTextForWidth($this->title, $availableTextWidth, 2);
        $name = $this->firstname . ' ' . $this->lastname;

        $this->SetXY($this->printBorder, $this->printBorder);

        $this->addText($this->descCallNumber, $this->callNumber,   $availableTextWidth);
        $this->addText($this->descTitle,      $title,              $availableTextWidth);
        $this->addText($this->descYear,       $this->year,         $availableTextWidth);
        $this->addText($this->descVolume,     $this->volume,       $availableTextWidth);
        $this->addText($this->descIssue,      $this->issue,        $availableTextWidth);
        $this->addText($this->descOrderedAt,  $this->orderedAt,    $availableTextWidth);
        $this->addText("bearbeitet am",       null,                $availableTextWidth);

        $this->SetXY($this->printBorder, $this->dinA4height - 60);

        $this->addText("Leihfrist",           null,                $availableTextWidth);
        $this->SetXY($this->GetX(), $this->GetY() + 5);
        $this->addText($this->descUserNumber, $this->username,     $availableTextWidth);
        $this->addText($this->descName,       $name,               $availableTextWidth,
                       false, 'B', self::DEFAULT_FONT_SIZE + 2);
    }

    /**
     * Adds a text to the pdf. X, Y coordinates have to be set beforehand.
     *
     * @param string $description Translation key of the description.
     * @param string $text Text to be added.
     * @param int $cellWidth Width of the text cell.
     * @param bool $asTable Format text as a table? If true, there will be a column
     *                            for descriptions and a column for the text.
     * @param string $textFontStyle
     * @param int $textFontSize
     */
    protected function addText($description, $text, $cellWidth, $asTable = false, $textFontStyle = '', $textFontSize = self::DEFAULT_FONT_SIZE) {
        $description = $this->translator->translate($description);
        $description = $description ? $description . ':' : '';
        $spaceBetweenLines = 1;

        $tableOffset = $asTable ? 25 : 0;
        $x = $this->GetX();
        $y = $this->GetY() + 2;
        $this->SetXY($x, $y);

        // save font style and size to restore it after adding the text
        $tmpFontSize = $this->FontSizePt;
        $tmpFontStyle = $this->FontStyle;

        $this->SetFont($this->FontFamily, 'B');
        $this->MultiCell($cellWidth, $this->FontSize + $spaceBetweenLines, $description);
        $this->SetXY(
            !$asTable ? $x : $x + $tableOffset,
            !$asTable ? $this->GetY() : $y
        );

        $this->SetFont($this->FontFamily, $textFontStyle, $textFontSize);
        $this->MultiCell($cellWidth - $tableOffset, $this->FontSize + $spaceBetweenLines, $text, 0, 'L');
        $this->SetXY($x, $this->GetY());

        // restore font style and size
        $this->SetFont($this->FontFamily, $tmpFontStyle, $tmpFontSize);
    }

    /**
     * Set title data.
     *
     * @param string $title
     */
    public function setWorkTitle ($title) {
        $this->title = $title;
    }

    /**
     * Shortens a text to fit in a specified width.
     *
     * @param string $string      Text to shorten.
     * @param int    $widthInMM   Max width for the text after shortening.
     * @param int    $wantedLines Lines the text can fill.
     *
     * @return string
     */
    protected function shortenTextForWidth($string, $widthInMM, $wantedLines = 1) {
        // Base functionality taken from fpdf::MultiCell
        $widthMax = ($widthInMM - 2 * $this->cMargin);
        $charWidth = &$this->CurrentFont['cw'];
        $result = "";

        // Get string length
        $string = str_replace("\r", '', (string) $string);
        $numberBytes = mb_strlen($string, 'utf-8');
        while($numberBytes > 0 && mb_substr($string, $numberBytes - 1, 1, 'utf-8') == "\n") {
            $numberBytes--;
        }

        $indexSeparator = -1;
        $indexString = 0;
        $j = 0;
        $length = 0;
        $numberSeparators = 0;
        $numberLines = 1;
        while($indexString < $numberBytes) {
            if($numberLines > $wantedLines) {
                break;
            }

            // Get next character
            $char = mb_substr($string, $indexString, 1, 'UTF-8');
            if($numberLines < $wantedLines && $char == ' ') {
                $indexSeparator = $indexString;
                $numberSeparators++;
            }

            $length += $this->GetStringWidth($char);

            if($length > $widthMax) {
                // Automatic line break
                if($indexSeparator == -1) {
                    if($indexString == $j) {
                        $indexString++;
                    }
                    $result .= substr($string, $j, $indexString - $j);
                }
                else {
                    $result .= substr($string, $j,$indexSeparator - $j + 1);
                    $indexString = $indexSeparator + 1;
                }
                $indexSeparator = -1;
                $j = $indexString;
                $length = 0;
                $numberSeparators = 0;
                $numberLines++;
            }
            else {
                $indexString++;
            }

            if($indexString >= $numberBytes && $length <= $widthMax) {
                $result .= substr($string, $j, $indexString - $j);
            }
        }

        // Replace the last three chars with 3 dots if the text was shortened.
        if($numberBytes > strlen($result)) {
            $result = substr($result, 0 , -3) . "...";
        }

        return $result;
    }

    /**
     * Set issue data.
     *
     * @param string $issue
     */
    public function setIssue($issue) {
        $this->issue = $issue;
    }

    /**
     * Set pages data.
     *
     * @param string $pages
     */
    public function setPages($pages) {
        $this->requestPages = $pages;
    }

    /**
     * Set firstname data.
     *
     * @param string $firstname
     */
    public function setFirstName($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * Set lastname data.
     *
     * @param string $lastname
     */
    public function setLastName($lastname) {
        $this->lastname = $lastname;
    }

    /**
     * Set user id data.
     *
     * @param string $userId
     */
    public function setUserName($userId) {
        $this->username = $userId;
    }

    /**
     * Set callnumber data.
     *
     * @param string $callNumber
     */
    public function setCallNumber($callNumber) {
        $this->callNumber = $callNumber;
    }

    /**
     * Set year data.
     *
     * @param string $year
     */
    public function setYear($year) {
        $this->year = $year;
    }

    /**
     * Set volume data.
     *
     * @param string $volume
     */
    public function setVolume($volume) {
        $this->volume = $volume;
    }

    /**
     * Set comment data.
     *
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }
}