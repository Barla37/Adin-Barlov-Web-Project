<?php
// Membership routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

Flight::route('GET /memberships', function () {
    Flight::json(Flight::membership_service()->get_all());
});

Flight::route('GET /memberships/@id:[0-9]+', function ($id) {
    Flight::json(Flight::membership_service()->get_by_id((int) $id));
});

Flight::route('POST /memberships', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::membership_service()->add($data), 201);
});

Flight::route('PUT|PATCH /memberships/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::membership_service()->update($data, (int) $id));
});

Flight::route('DELETE /memberships/@id:[0-9]+', function ($id) {
    Flight::membership_service()->delete((int) $id);
    Flight::json(['message' => 'Membership deleted']);
});
