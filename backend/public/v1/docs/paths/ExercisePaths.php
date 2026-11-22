<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/ExerciseRoutes.php (data comes from the external wger API).
 */
class ExercisePaths
{
    #[OA\Get(
        path: '/exercises',
        tags: ['exercises'],
        summary: 'Exercises from the external wger API',
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Page size, 1-50 (default 12)', example: 12, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'offset', in: 'query', required: false, description: 'How many exercises to skip (default 0)', example: 0, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'One page of exercises', content: new OA\JsonContent(ref: '#/components/schemas/ExercisePage')),
            new OA\Response(response: 400, description: 'Invalid limit or offset', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 503, description: 'wger is not available (timeout, network or server error)', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExercises() {}
}
