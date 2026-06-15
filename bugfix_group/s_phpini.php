<?php
header('Content-Type: text/plain; charset=utf-8');
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
