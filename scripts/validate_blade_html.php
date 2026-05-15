<?php
// Simple HTML validation using DOMDocument and libxml
// Usage: php scripts/validate_blade_html.php

$path = __DIR__ . '/../resources/views/cordon_staff/walkInsLogs.blade.php';
if (!file_exists($path)) {
    echo "File not found: $path\n";
    exit(1);
}

$html = file_get_contents($path);
if ($html === false) {
    echo "Failed to read file: $path\n";
    exit(1);
}

$html_for_parse = $html;
// Mask common Blade/PHP constructs that contain raw ampersands or chars that break XML parsing
$html_for_parse = str_replace('&&', '&amp;&amp;', $html_for_parse);
// Mask Blade/PHP directives with harmless placeholders to avoid breaking HTML structure
$html_for_parse = str_replace(['{{', '}}', '@if', '@foreach', '@csrf', '@endif', '@endforeach'], ['BLD_OP', 'BLD_CL', 'BLADE_IF', 'BLADE_FOREACH', 'BLADE_CSRF', 'BLADE_ENDIF', 'BLADE_ENDFOREACH'], $html_for_parse);

libxml_use_internal_errors(true);
$doc = new DOMDocument();
// Suppress warnings; loadHTML expects a full HTML document
$loaded = $doc->loadHTML($html_for_parse, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$errors = libxml_get_errors();

if (empty($errors)) {
    echo "No libxml parsing errors detected.\n";
} else {
    echo "libxml parsing errors:\n";
    foreach ($errors as $err) {
        echo sprintf("- [Line %d] %s (code %d)\n", $err->line, trim($err->message), $err->code);
    }
}
libxml_clear_errors();

// Additional structural checks: find script tags inside anchor tags
$xpath = new DOMXPath($doc);
$nodes = $xpath->query('//a//script');
if ($nodes->length > 0) {
    echo "Found script tags nested inside <a> elements: {$nodes->length}\n";
    foreach ($nodes as $i => $n) {
        $parent = $n->parentNode;
        $a = $parent->C14N();
        echo "- Script inside anchor snippet: " . substr(trim($a), 0, 200) . "...\n";
    }
} else {
    echo "No <script> tags nested inside <a> elements found.\n";
}

// Find duplicate script src includes
$scripts = [];
foreach ($doc->getElementsByTagName('script') as $script) {
    $src = $script->getAttribute('src');
    if ($src) {
        if (!isset($scripts[$src])) $scripts[$src] = 0;
        $scripts[$src]++;
    }
}
$dups = array_filter($scripts, function($c){return $c>1;});
if (!empty($dups)) {
    echo "Duplicate script includes detected:\n";
    foreach ($dups as $src => $count) {
        echo "- $src included $count times\n";
    }
} else {
    echo "No duplicate script src entries detected.\n";
}

// Quick tag balance check: ensure <html>, <head>, <body> present
$hasHtml = $doc->getElementsByTagName('html')->length > 0;
$hasHead = $doc->getElementsByTagName('head')->length > 0;
$hasBody = $doc->getElementsByTagName('body')->length > 0;
echo "Tag presence: html=".($hasHtml?"yes":"no").", head=".($hasHead?"yes":"no").", body=".($hasBody?"yes":"no")."\n";

// Basic count of div tags in source (raw file)
$raw = $html;
$openDivs = substr_count($raw, '<div');
$closeDivs = substr_count($raw, '</div>');
echo "Raw file div counts: open={$openDivs}, close={$closeDivs}\n";

// Line-by-line div balance check to locate problematic closing tags
$lines = explode("\n", $raw);
$balance = 0;
$problemLines = [];
foreach ($lines as $ln => $text) {
    $o = substr_count($text, '<div');
    $c = substr_count($text, '</div>');
    $balance += ($o - $c);
    if ($balance < 0) {
        $problemLines[] = $ln + 1; // 1-based
        $balance = 0; // reset to continue searching
    }
}
if (!empty($problemLines)) {
    echo "Potential unmatched </div> at lines: " . implode(', ', $problemLines) . "\n";
} else {
    echo "No immediate unmatched </div> lines detected (by greedy scan). Final balance: {$balance}\n";
}

exit(0);
