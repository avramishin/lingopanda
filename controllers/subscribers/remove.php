<?php
$response = new JsonResponse();

try {

    if (r('id')) {
        if ($subscriber = LingopandaSubscribers::get(r('id'))) {
            $subscriber->remove();
            $response->affectedRows = db('lingopanda')->affectedRows();
        }
    }

    if (r('email')) {
        if ($subscriber = LingopandaSubscribers::findRow('email = ?', r('email'))) {
            $subscriber->remove();
            $response->affectedRows = db('lingopanda')->affectedRows();
        }
    }

    $response->send();
} catch (Exception $e) {
    $response->error($e->getMessage());
}