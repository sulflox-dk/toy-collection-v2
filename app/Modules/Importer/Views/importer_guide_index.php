<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Importer Guide</h1>
        <p class="text-muted small mb-0">What this actually is, and how to use it.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold">What this is</h5>
        <p class="mb-0">
            This isn't a generic file importer — it's a small web scraper for six specific vintage
            Star Wars collector-database websites. You paste a URL from one of those sites, it fetches
            the real page and pulls structured data out of the HTML (name, year, manufacturer, toy line,
            wave, SKU, accessories, images), and shows you a preview before anything touches your catalog.
        </p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">How it fits together — three pages, in this order</div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark rounded-pill">1</span>
                    <strong>Import Sources</strong>
                </div>
                <p class="small text-muted mb-0">
                    Each row here maps a website's domain to the PHP class that knows how to scrape it
                    (a "driver"). This is set up already — the six built-in sites are pre-registered and
                    active, so you shouldn't need to touch this page unless you're adding a new site
                    yourself (which needs a new driver class written first; the field is free text, not
                    a dropdown, so it has to match a real class name exactly).
                </p>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark rounded-pill">2</span>
                    <strong>Run Import</strong>
                </div>
                <p class="small text-muted mb-0">
                    This is the page you'll actually use. Paste a URL, click <strong>Analyze</strong>.
                    It figures out which site it's from, scrapes it, and shows you a preview grid — each
                    row flagged <span class="badge bg-success-subtle text-success">New</span>,
                    <span class="badge bg-warning-subtle text-warning">Conflict</span> (name already
                    exists in your catalog), or <span class="badge bg-secondary-subtle text-secondary">Linked</span>
                    (already imported before). Select the ones you want, click <strong>Import Selected</strong>.
                    Nothing is written until that click.
                </p>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark rounded-pill">3</span>
                    <strong>Import Logs</strong>
                </div>
                <p class="small text-muted mb-0">
                    A read-only audit trail — every import attempt, success or failure, with the actual
                    error message if one failed. Check here first if something didn't import the way
                    you expected.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">The six supported sites</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3">Site</th>
                    <th>Paste a URL like...</th>
                    <th>Bulk import?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-3 fw-bold">Action Figure 411</td>
                    <td class="small text-muted">Any single figure's detail page</td>
                    <td><span class="badge bg-secondary-subtle text-secondary">Single page only</span></td>
                </tr>
                <tr>
                    <td class="ps-3 fw-bold">Galactic Collector</td>
                    <td class="small text-muted">A listing page pulls up to 20 figures; a URL containing <code>/fig/</code> is one figure</td>
                    <td><span class="badge bg-success-subtle text-success">Bulk supported</span></td>
                </tr>
                <tr>
                    <td class="ps-3 fw-bold">Galactic Figures</td>
                    <td class="small text-muted">A URL containing <code>type=toyline</code> pulls up to 20 figures from that line</td>
                    <td><span class="badge bg-success-subtle text-success">Bulk supported</span></td>
                </tr>
                <tr>
                    <td class="ps-3 fw-bold">Jedi Temple Archives</td>
                    <td class="small text-muted">Any single figure's detail page</td>
                    <td><span class="badge bg-secondary-subtle text-secondary">Single page only</span></td>
                </tr>
                <tr>
                    <td class="ps-3 fw-bold">Star Wars Collector</td>
                    <td class="small text-muted">Any single figure's detail page</td>
                    <td><span class="badge bg-secondary-subtle text-secondary">Single page only</span></td>
                </tr>
                <tr>
                    <td class="ps-3 fw-bold">The Toy Collectors Guide</td>
                    <td class="small text-muted">A listing page (one without <code>#item-</code> in the URL) pulls up to 20 figures</td>
                    <td><span class="badge bg-success-subtle text-success">Bulk supported</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Worth knowing before you rely on it</div>
    <div class="card-body">
        <ul class="mb-0">
            <li class="mb-2">
                <strong>Manufacturer and toy line only match on an exact name.</strong> If the scraped
                site says "Kenner" and you already have a manufacturer named exactly "Kenner", it links
                automatically. Anything that doesn't match exactly — different spelling, extra
                whitespace, a name you haven't created yet — is silently left blank rather than guessed
                at or auto-created. Worth a quick check on the Catalog Toys page after importing.
            </li>
            <li class="mb-2">
                <strong>Universe, product type, and entertainment source are never set by an import.</strong>
                The scrapers don't have a reliable way to know these, so every imported toy needs a
                short manual pass afterward if you want those filled in.
            </li>
            <li class="mb-2">
                <strong>These are screen-scrapers reading raw HTML</strong>, not official APIs — several
                use regex against page text (looking for literal "Year:", "Series:", "Manufacturer:"
                labels) rather than a stable data feed. If one of these six sites redesigns their pages,
                that driver can start returning empty or wrong fields with no warning beyond what shows
                up (or doesn't) in the preview. Always look over the preview before importing, not just
                after.
            </li>
            <li>
                <strong>Bulk imports cap at 20 items</strong> per overview page, to keep a single click
                from hammering someone else's website.
            </li>
        </ul>
    </div>
</div>

<?php if (!empty($sources)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Currently configured on this install</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3">Name</th>
                    <th>Base URL</th>
                    <th>Driver class</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sources as $s): ?>
                    <tr>
                        <td class="ps-3"><?= $e($s['name']) ?></td>
                        <td class="text-muted small"><?= $e($s['base_url']) ?></td>
                        <td class="text-muted small"><code><?= $e($s['driver_class']) ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
