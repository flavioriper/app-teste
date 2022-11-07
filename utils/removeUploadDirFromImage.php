<?php

function removeUploadDirFromImage($path, $size = 0) {
    $wordpress_upload_dir = wp_upload_dir()['baseurl'].'/';
    return $size > 0 ? $path : str_replace($wordpress_upload_dir, '', $path);
}