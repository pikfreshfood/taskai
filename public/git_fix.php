<?php
// Run this once: php public/git_fix.php
// Then DELETE this file.

$repoDir = dirname(__DIR__);
chdir($repoDir);

echo "Backing up public/autho.json...\n";
if (file_exists('public/autho.json')) {
    copy('public/autho.json', 'public/autho.json.bak');
}

echo "Removing public/error_log...\n";
if (file_exists('public/error_log')) {
    unlink('public/error_log');
}

echo "Fetching latest from remote...\n";
passthru('git fetch origin 2>&1', $code);
if ($code !== 0) { echo "Git fetch failed.\n"; exit(1); }

echo "Resetting to origin/main...\n";
passthru('git reset --hard origin/main 2>&1', $code);
if ($code !== 0) { echo "Git reset failed.\n"; exit(1); }

echo "Restoring public/autho.json...\n";
if (file_exists('public/autho.json.bak')) {
    copy('public/autho.json.bak', 'public/autho.json');
    unlink('public/autho.json.bak');
}

echo "Done. Git is now in sync.\n";
