<?php

spl_autoload_register(function($class) {
   require_once("includes/$class.php");
});

$table = new OutputPage();

try {
    $table->render();
} catch (HttpException $e) {
    $table->renderError(500, "Something went wrong");
} catch (Exception $e) {
    die("Unable to load page.");
}