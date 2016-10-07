<?php

namespace ThULB\ILS\Driver;
use VuFind\ILS\Driver\PAIA as OriginalPAIA;

/**
 * ThULB extension for the PAIA/DAIA driver
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class PAIA extends OriginalPAIA
{
    const DAIA_DOCUMENT_ID_PREFIX = 'http://uri.gbv.de/document/opac-de-27:';
    
    /**
     * Overrides the function in the DAIA driver class, to execute additional
     * steps.
     * 
     * @param string $id           Record Id of the DAIA document in question.
     * @param string $daiaResponse Raw response from DAIA request.
     *
     * @return Array|DOMNode|null   The DAIA document identified by id and
     *                                  type depending on daiaResponseFormat.
     * @throws ILSException
     */
    protected function extractDaiaDoc($id, $daiaResponse)
    {
        return parent::extractDaiaDoc(
                    $id,
                    $this->sanitizeDaiaDocumentIds($daiaResponse)
                );
    }
    
    /**
     * This function ensures, that the condition "DAIA documents should use URIs
     * as value for id" (see DAIA Driver class comment) is fulfilled even for a
     * GBV response.
     * 
     * @see \VuFind\ILS\Driver\DAIA::extractDaiaDoc() for the failing condition
     * @todo relax the condition in vufinds core DAIA driver, because it
     *      contradicts the documentation of DAIA (http://gbv.github.io/daia/daia.html#documents
     *      talks about a "globally unique identifier of the document" and not
     *      about the request identifier in the id field)
     */
    private function sanitizeDaiaDocumentIds($daiaResponse)
    {
        return str_replace(self::DAIA_DOCUMENT_ID_PREFIX, '', $daiaResponse);
    }
}
