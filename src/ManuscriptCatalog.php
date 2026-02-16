<?php
/**
 * ManuscriptCatalog - Scans and indexes TEI/XML files in a data directory.
 *
 * Provides a simple catalog/inventory of all available manuscripts,
 * including extracted metadata for each. This mimics the kind of
 * index functionality found in Digital Humanities archives.
 *
 * @author  Nikhil Shah
 * @license MIT
 */

class ManuscriptCatalog
{
    /** @var string Directory containing TEI/XML files */
    private string $dataDir;

    /** @var TEITransformer */
    private TEITransformer $transformer;

    /** @var array|null Cached catalog entries */
    private ?array $catalog = null;

    /**
     * @param string         $dataDir     Path to directory of .xml files.
     * @param TEITransformer $transformer Transformer instance (for metadata extraction).
     */
    public function __construct(string $dataDir, TEITransformer $transformer)
    {
        if (!is_dir($dataDir)) {
            throw new InvalidArgumentException("Data directory not found: {$dataDir}");
        }
        $this->dataDir     = rtrim($dataDir, '/');
        $this->transformer = $transformer;
    }

    /**
     * Get all manuscripts in the catalog.
     *
     * @return array List of manuscripts with slug, filename, and metadata.
     */
    public function getAll(): array
    {
        if ($this->catalog === null) {
            $this->catalog = $this->scan();
        }
        return $this->catalog;
    }

    /**
     * Find a single manuscript by its URL slug.
     *
     * @param  string     $slug  The URL-safe identifier (filename without .xml).
     * @return array|null        Manuscript entry or null if not found.
     */
    public function findBySlug(string $slug): ?array
    {
        foreach ($this->getAll() as $entry) {
            if ($entry['slug'] === $slug) {
                return $entry;
            }
        }
        return null;
    }

    /**
     * Get the full filesystem path for a manuscript slug.
     */
    public function getFilePath(string $slug): ?string
    {
        $entry = $this->findBySlug($slug);
        return $entry ? $entry['path'] : null;
    }

    /**
     * Scan the data directory for XML files and extract their metadata.
     */
    private function scan(): array
    {
        $files   = glob($this->dataDir . '/*.xml');
        $catalog = [];

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $slug     = pathinfo($filename, PATHINFO_FILENAME);

            try {
                $metadata = $this->transformer->extractMetadata($filePath);
            } catch (\Exception $e) {
                continue;
            }

            $catalog[] = [
                'slug'     => $slug,
                'filename' => $filename,
                'path'     => $filePath,
                'metadata' => $metadata,
            ];
        }

        usort($catalog, function ($a, $b) {
            return strcasecmp($a['metadata']['title'], $b['metadata']['title']);
        });

        return $catalog;
    }

    /**
     * Search manuscripts by keyword across title, subtitle, and description.
     *
     * @param  string $query  Search term.
     * @return array          Matching catalog entries.
     */
    public function search(string $query): array
    {
        $query   = strtolower(trim($query));
        $results = [];

        foreach ($this->getAll() as $entry) {
            $searchable = strtolower(implode(' ', [
                $entry['metadata']['title'],
                $entry['metadata']['subtitle'],
                $entry['metadata']['description'],
                $entry['metadata']['author'],
            ]));

            if (strpos($searchable, $query) !== false) {
                $results[] = $entry;
            }
        }

        return $results;
    }
}
