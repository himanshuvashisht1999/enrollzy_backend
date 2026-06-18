<?php
$files = [
    'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\school-marquees\\index.blade.php',
    'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\institute-marquees\\index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Edit Modal Search
    $editSearch = <<<EOT
                                                <div class="mb-3">
                                                    <label for="sort_order{{ \$marquee->id }}" class="form-label">Sort Order</label>
                                                    <input type="number" class="form-control" id="sort_order{{ \$marquee->id }}" name="sort_order" value="{{ \$marquee->sort_order }}">
                                                </div>
EOT;
    $editReplace = <<<EOT
                                                <div class="mb-3">
                                                    <label for="sort_order{{ \$marquee->id }}" class="form-label">Sort Order</label>
                                                    <input type="number" class="form-control" id="sort_order{{ \$marquee->id }}" name="sort_order" value="{{ \$marquee->sort_order }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="logo_url{{ \$marquee->id }}" class="form-label">Logo Target URL</label>
                                                    <input type="text" class="form-control" id="logo_url{{ \$marquee->id }}" name="logo_url" value="{{ \$marquee->logo_url }}" placeholder="https://example.com">
                                                </div>
EOT;

    // Add Modal Search
    $addSearch = <<<EOT
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                        </div>
EOT;
    $addReplace = <<<EOT
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="logo_url" class="form-label">Logo Target URL</label>
                            <input type="text" class="form-control" id="logo_url" name="logo_url" placeholder="https://example.com">
                        </div>
EOT;

    $content = str_replace($editSearch, $editReplace, $content);
    $content = str_replace($addSearch, $addReplace, $content);
    
    file_put_contents($file, $content);
}

echo "Admin views updated successfully.";
