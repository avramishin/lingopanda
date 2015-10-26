<?php
$response = new JsonResponse();

try {

    if (!filter_var(r('email'), FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please provide valid email address!");
    }

    if (!$subscriber = LingopandaSubscribers::findRow('email = ?', r('email'))) {
        $subscriber = new LingopandaSubscribers();
        $subscriber->email = r('email');
        $subscriber->firstname = r('firstname');
        $subscriber->firstname = r('lastname');
        $subscriber->created = dbtime();
        $subscriber->id = $subscriber->insert();
    }

    $response->data = $subscriber;
    $response->send();
} catch (Exception $e) {
    $response->error($e->getMessage());
}