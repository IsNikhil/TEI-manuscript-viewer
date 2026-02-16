# TEI Manuscript Viewer

A PHP web application that transforms TEI/XML encoded manuscripts into readable, styled HTML pages using XSLT. Built as a demonstration of the same technology pipeline used in Digital Humanities archival projects like the Early Ruskin Manuscripts (erm.selu.edu).

## What It Does

This app takes scholarly manuscripts encoded in [TEI P5](https://tei-c.org/) (Text Encoding Initiative) XML format, transforms them through XSLT stylesheets, and serves them as styled, interactive web pages via PHP.

**Features:**
- XSLT Transformation Engine using PHP's `XSLTProcessor` and `DOMDocument`
- Manuscript Catalog with automatic metadata extraction
- Search functionality across titles, authors, and descriptions
- Editorial Markup Rendering (deletions, additions, corrections, gaps)
- Named Entity Highlighting (person names, place names)
- Simple JSON API

## Tech Stack

| Technology | Role |
|---|---|
| **PHP 8+** | Web server, routing, XSLT processing |
| **XML/TEI P5** | Manuscript encoding standard |
| **XSLT 1.0** | XML-to-HTML transformation |
| **DOMDocument** | XML parsing and XPath queries |
| **XSLTProcessor** | XSLT execution in PHP |

## Project Structure

```
tei-manuscript-viewer/
├── public/
│   └── index.php            # Entry point, router
├── src/
│   ├── TEITransformer.php   # XSLT transformation engine
│   └── ManuscriptCatalog.php # Document indexer
├── data/
│   ├── xml/                 # TEI-encoded manuscripts
│   └── xslt/
│       └── tei-to-html.xsl  # Transformation stylesheet
├── assets/css/style.css     # Scholarly-themed styles
└── README.md
```

## How to Run

**Requirements:** PHP 8.0+ with the `xsl` extension enabled.

```bash
# Clone the repo
git clone https://github.com/YOUR_USERNAME/tei-manuscript-viewer.git
cd tei-manuscript-viewer

# Start PHP's built-in development server
php -S localhost:8000 -t public

# Open in browser
# http://localhost:8000
```

No Composer dependencies. No frameworks. Just PHP, XML, and XSLT.

## How It Works

1. TEI/XML files are stored in `data/xml/` with full TEI headers and content
2. The `TEITransformer` class uses `XSLTProcessor` to transform XML to HTML
3. The `ManuscriptCatalog` scans XML files and extracts metadata via `DOMXPath`
4. The router serves the catalog, individual manuscripts, and a JSON API
5. XSLT maps TEI elements to semantic HTML with visual styling

## API Endpoints

| Endpoint | Description |
|---|---|
| `/api/manuscripts` | List all manuscripts with metadata |
| `/api/manuscripts/{slug}` | Get metadata for a single manuscript |

## Inspiration

This project demonstrates the same XML/TEI + XSLT + PHP transformation pipeline used in scholarly digital editions like [The Early Ruskin Manuscripts](https://erm.selu.edu/) at Southeastern Louisiana University.

## Author

**Nikhil Shah** — Built to demonstrate proficiency with the Digital Humanities tech stack

## License

MIT License
