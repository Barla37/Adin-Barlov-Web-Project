<?php
// Trainer routes. A route reads the input, calls ONE service method and answers with JSON.
// Validation and business rules live in the service; errors are handled in index.php.

Flight::route('GET /trainers', function () {
    Flight::json(Flight::trainer_service()->get_all());
});

Flight::route('GET /trainers/@id:[0-9]+', function ($id) {
    Flight::json(Flight::trainer_service()->get_by_id((int) $id));
});

Flight::route('POST /trainers', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::trainer_service()->add($data), 201);
});

Flight::route('PUT|PATCH /trainers/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::trainer_service()->update($data, (int) $id));
});

Flight::route('DELETE /trainers/@id:[0-9]+', function ($id) {
    Flight::trainer_service()->delete((int) $id);
    Flight::json(['message' => 'Trainer deleted']);
});
