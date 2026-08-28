# SEO and AI discoverability report

## Public surface audited

Indexable pages are the home page, unfiltered radio and TV directories, eligible published radio and TV detail pages, and country detail pages containing published media. Filter, sort, search, and pagination query URLs use `noindex, follow` with a canonical link to their base directory. Authenticated admin routes return an `X-Robots-Tag` header that blocks indexing. API and authentication paths are excluded in `robots.txt`.

## Implemented discovery infrastructure

- Shared canonical, description, robots, Open Graph, Twitter Card, and JSON-LD metadata.
- Visible breadcrumbs and `BreadcrumbList` schema on radio and TV detail pages.
- A sitemap index split into pages, countries, radio, and TV sitemaps.
- Eligibility rules: media must be published and have a primary stream that is not marked offline.
- Stateless, cacheable `robots.txt`, sitemap, RSS, `llms.txt`, `llms-full.txt`, and `ai.txt` endpoints.
- A recently added RSS feed and factual AI-readable product/source documentation.
- A 1200 by 630 Wavexa social sharing image.

## Content and claim policy

Wavexa currently describes radio and supported live TV as implemented. Podcast, account, recommendation, globe, and mobile functionality is identified as planned in AI-readable documentation rather than represented as currently available. Third-party streams and metadata retain provider attribution and a rights/availability caveat.

## Deployment requirements

Set `APP_URL` to the final public HTTPS origin before generating caches or submitting the sitemap. The current development default is intentionally local and must not be used for launch. After deployment, verify all discovery endpoints from outside the network, submit `/sitemap.xml` to Google Search Console and Bing Webmaster Tools, and validate representative pages with schema and social-card tools.

Discovery files improve crawlability but cannot guarantee immediate indexing or search ranking. Indexing speed also depends on deployment accessibility, content quality, links, crawl demand, and each crawler's policies.
