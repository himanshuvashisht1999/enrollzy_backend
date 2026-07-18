<?php
$files = [
    'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\school-marquees\\index.blade.php',
    'c:\\xampp\\htdocs\\enrollzy_backend\\resources\\views\\admin\\institute-marquees\\index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Add Logo Target URL to Add Modal
    $addSearch = <<<EOT
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
EOT;
    $addReplace = <<<EOT
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Target URL (Optional)</label>
                        <input type="url" name="logo_url" class="form-control" placeholder="https://example.com">
                    </div>
EOT;
    $content = str_replace($addSearch, $addReplace, $content);

    // Add Logo Target URL to Edit Modal
    $editSearch = <<<EOT
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control">
                    </div>
EOT;
    $editReplace = <<<EOT
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Target URL (Optional)</label>
                        <input type="url" name="logo_url" id="edit_url" class="form-control" placeholder="https://example.com">
                    </div>
EOT;
    $content = str_replace($editSearch, $editReplace, $content);

    // Add data-url to Edit Button
    $btnSearch = <<<EOT
                        data-sort="{{ \$item->sort_order }}"
                        data-logo="{{ asset(\$item->logo) }}">
EOT;
    $btnReplace = <<<EOT
                        data-sort="{{ \$item->sort_order }}"
                        data-url="{{ \$item->logo_url }}"
                        data-logo="{{ asset(\$item->logo) }}">
EOT;
    $content = str_replace($btnSearch, $btnReplace, $content);

    // Add to JS
    $jsSearch = <<<EOT
        let sort = $(this).data('sort');
        let logo = $(this).data('logo');
EOT;
    $jsReplace = <<<EOT
        let sort = $(this).data('sort');
        let url = $(this).data('url');
        let logo = $(this).data('logo');
EOT;
    $content = str_replace($jsSearch, $jsReplace, $content);

    $jsSearch2 = <<<EOT
        $('#edit_sort').val(sort);
        $('#editPreview').attr('src', logo);
EOT;
    $jsReplace2 = <<<EOT
        $('#edit_sort').val(sort);
        $('#edit_url').val(url);
        $('#editPreview').attr('src', logo);
EOT;
    $content = str_replace($jsSearch2, $jsReplace2, $content);

    file_put_contents($file, $content);
}

echo "Modals updated successfully.";
