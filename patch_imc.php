<?php
$file = 'c:\\xampp\\htdocs\\enrollzy_backend\\app\\Http\\Controllers\\Admin\\InstituteMarqueeController.php';
$content = file_get_contents($file);

// Store method validation
$storeValSearch = <<<EOT
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
EOT;
$storeValReplace = <<<EOT
            'subheading' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'logo_url'   => 'nullable|string|max:255',
        ]);
EOT;
$content = str_replace($storeValSearch, $storeValReplace, $content);

// Data array
$dataSearch = <<<EOT
        \$data = \$request->only(['name', 'heading', 'subheading', 'sort_order']);
EOT;
$dataReplace = <<<EOT
        \$data = \$request->only(['name', 'heading', 'subheading', 'sort_order', 'logo_url']);
EOT;
$content = str_replace($dataSearch, $dataReplace, $content);

file_put_contents($file, $content);
echo "InstituteMarqueeController updated successfully.";
