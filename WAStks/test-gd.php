<?php
if (function_exists('imagecreatefromwebp')) {
    echo "WebP is supported!";
} else {
    echo "WebP is NOT supported. Loaded extensions: <pre>";
    print_r(get_loaded_extensions());
    echo "</pre>";
}
?>