<?php

spl_autoload_register(function($class) {
   require_once("includes/$class.php");
});

$output_page = new OutputPage();

try {
    $output_page->render();
} catch (HttpException $e) {
    $output_page->renderError(500, "Something went wrong");
} catch (Exception $e) {
    die("Unable to load page.");
}