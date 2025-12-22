<?php
/**
 * Script to fix all asset paths in view files
 * Run this once to update all view files to use the asset() helper
 */

$viewsDir = __DIR__ . '/app/views';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir),
    RecursiveIteratorIterator::SELF_FIRST
);

$patterns = [
    '/href="\.\.\/\.\.\/public\/(css\/[^"]+)"/' => 'href="<?= asset(\'$1\') ?>"',
    '/src="\.\.\/\.\.\/public\/(js\/[^"]+)"/' => 'src="<?= asset(\'$1\') ?>"',
    '/href="\.\.\/\.\.\/\.\.\/public\/(css\/[^"]+)"/' => 'href="<?= asset(\'$1\') ?>"',
    '/src="\.\.\/\.\.\/\.\.\/public\/(js\/[^"]+)"/' => 'src="<?= asset(\'$1\') ?>"',
];

$fixed = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // Add path helper include if not present and file has asset paths
        if (preg_match('/\.\.\/\.\.\/public\/(css|js)/', $content) && 
            !strpos($content, 'path_helper.php') && 
            !strpos($content, 'BASE_ASSETS_PATH')) {
            
            // Find the first PHP tag or head tag
            if (preg_match('/<head>/', $content)) {
                $content = preg_replace(
                    '/<head>/',
                    "<?php\n    // Include path helper if not already included\n    if (!defined('BASE_ASSETS_PATH')) {\n        require_once __DIR__ . '/path_helper.php';\n    }\n?>\n<head>",
                    $content,
                    1
                );
            } elseif (preg_match('/^<\?php/', $content)) {
                $content = preg_replace(
                    '/^<\?php/',
                    "<?php\n// Include path helper if not already included\nif (!defined('BASE_ASSETS_PATH')) {\n    require_once __DIR__ . '/path_helper.php';\n}",
                    $content,
                    1
                );
            }
        }
        
        // Replace asset paths
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            $fixed++;
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}

echo "\nFixed $fixed files.\n";

