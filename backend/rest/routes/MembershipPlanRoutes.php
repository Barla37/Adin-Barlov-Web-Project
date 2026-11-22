<?php
// Membership plan routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

Flight::route('GET /membership-plans', function () {
    Flight::json(Flight::membership_plan_service()->get_all());
});

Flight::route('GET /membership-plans/@id:[0-9]+', function ($id) {
    Flight::json(Flight::membership_plan_service()->get_by_id((int) $id));
});

Flight::route('POST /membership-plans', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::membership_plan_service()->add($data), 201);
});

Flight::route('PUT|PATCH /membership-plans/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::membership_plan_service()->update($data, (int) $id));
});

Flight::route('DELETE /membership-plans/@id:[0-9]+', function ($id) {
    Flight::membership_plan_service()->delete((int) $id);
    Flight::json(['message' => 'Membership plan deleted']);
});
