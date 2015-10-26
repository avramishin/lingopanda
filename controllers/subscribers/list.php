<?php
$response = new JsonResponse();

try {
    $response->data = LingopandaSubscribers::getAll('id DESC');
    $response->send();
} catch (Exception $e) {
    $response->error($e->getMessage());
}