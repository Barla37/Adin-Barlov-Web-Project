<?php
// Exercises from the external wger API (see rest/services/ExerciseService.php).
// GET /exercises?limit=12&offset=0

Flight::route('GET /exercises', function () {
    $query = Flight::request()->query;
    $limit = $query['limit'] ?? 12;
    $offset = $query['offset'] ?? 0;
    Flight::json(Flight::exercise_service()->get_exercises($limit, $offset));
});
