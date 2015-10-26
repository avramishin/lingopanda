<?php
$response = new JsonResponse();

try {
    $response->send();
} catch (Exception $e) {
    $response->error($e->getMessage());
}