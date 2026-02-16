<?php
/**
 * TEITransformer - Transforms TEI/XML manuscripts to HTML using XSLT.
 *
 * This class handles the core transformation pipeline:
 *   1. Load a TEI-encoded XML document
 *   2. Apply an XSLT stylesheet to produce HTML
 *   3. Return the rendered HTML for display
 *
 * It uses PHP's built-in XSLTProcessor and DOMDocument classes,
 * which are the same tools used in production Digital Humanities
 * projects like the Early Ruskin Manuscripts (erm.selu.edu).
 *
 * @author  Nikhil Shah
 * @license MIT
 */

class TEITransformer
{
    /** @var string Path to the XSLT stylesheet */
    private string $xsltPath;

    /** @var XSLTProcessor Cached XSLT processor instance */
    private ?XSLTProcessor $processor = null;

    /**
     * @param string $xsltPath Absolute path to the .xsl stylesheet file.
     */
    public function __construct(string $xsltPath)
    {
        if (!file_exists($xsltPath)) {
            throw new InvalidArgumentException("XSLT stylesheet not found: {$xsltPath}");
        }
        $this->xsltPath = $xsltPath;
    }

    /**
     * Initialize the XSLT processor (lazy-loaded, cached).
     */
    private function getProcessor(): XSLTProcessor
    {
        if ($this->processor === null) {
            $xslDoc = new DOMDocument();
            $xslDoc->load($this->xsltPath);

            $this->processor = new XSLTProcessor();
            $this->processor->importStylesheet($xslDoc);
        }
        return $this->processor;
    }

    /**
     * Transform a TEI/XML file into an HTML string.
     *
     * @param  string $xmlPath  Path to the TEI XML file.
     * @return string           The resulting HTML fragment.
     * @throws RuntimeException If the XML file cannot be loaded or transformed.
     */
    public function transform(string $xmlPath): string
    {
        if (!file_exists($xmlPath)) {
            throw new InvalidArgumentException("XML file not found: {$xmlPath}");
        }

        $xmlDoc = new DOMDocument();
        $loaded = $xmlDoc->load($xmlPath);

        if (!$loaded) {
            throw new RuntimeException("Failed to parse XML file: {$xmlPath}");
        }

        $result = $this->getProcessor()->transformToXml($xmlDoc);

        if ($result === false) {
            throw new RuntimeException("XSLT transformation failed for: {$xmlPath}");
        }

        return $result;
    }

    /**
     * Extract metadata from a TEI XML file without full transformation.
     * Useful for building document listings / catalogs.
     *
     * @param  string $xmlPath  Path to the TEI XML file.
     * @return array            Associative array of metadata fields.
     */
    public function extractMetadata(string $xmlPath): array
    {
        if (!file_exists($xmlPath)) {
            throw new InvalidArgumentException("XML file not found: {$xmlPath}");
        }

        $xmlDoc = new DOMDocument();
        $xmlDoc->load($xmlPath);

        $xpath = new DOMXPath($xmlDoc);
        $xpath->registerNamespace('tei', 'http://www.tei-c.org/ns/1.0');

        return [
            'title'      => $this->xpathValue($xpath, '//tei:titleStmt/tei:title[@type="main"]'),
            'subtitle'   => $this->xpathValue($xpath, '//tei:titleStmt/tei:title[@type="sub"]'),
            'author'     => trim(
                $this->xpathValue($xpath, '//tei:titleStmt/tei:author/tei:persName/tei:forename') . ' ' .
                $this->xpathValue($xpath, '//tei:titleStmt/tei:author/tei:persName/tei:surname')
            ),
            'manuscript' => $this->xpathValue($xpath, '//tei:msIdentifier/tei:idno'),
            'repository' => $this->xpathValue($xpath, '//tei:msIdentifier/tei:repository'),
            'date'       => $this->xpathValue($xpath, '//tei:origDate'),
            'extent'     => $this->xpathValue($xpath, '//tei:extent'),
            'description'=> $this->xpathValue($xpath, '//tei:msContents/tei:msItem/tei:note'),
        ];
    }

    /**
     * Helper: get a single text value from an XPath query.
     */
    private function xpathValue(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }
}
