<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/TrainerRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class TrainerPaths
{
    #[OA\Get(
        path: '/trainers',
        tags: ['trainers'],
        summary: 'List all trainers',
        responses: [
            new OA\Response(response: 200, description: 'List of trainers', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Trainer')))
        ]
    )]
    public function getTrainers() {}

    #[OA\Get(
        path: '/trainers/{id}',
        tags: ['trainers'],
        summary: 'Get one trainer',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The trainer', content: new OA\JsonContent(ref: '#/components/schemas/Trainer')),
            new OA\Response(response: 404, description: 'Trainer not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getTrainerById() {}

    #[OA\Post(
        path: '/trainers',
        tags: ['trainers'],
        summary: 'Create a trainer',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TrainerInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created trainer', content: new OA\JsonContent(ref: '#/components/schemas/Trainer')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postTrainer() {}

    #[OA\Put(
        path: '/trainers/{id}',
        tags: ['trainers'],
        summary: 'Update a trainer (only the sent fields change; PATCH works the same)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TrainerInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated trainer', content: new OA\JsonContent(ref: '#/components/schemas/Trainer')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Trainer not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putTrainer() {}

    #[OA\Delete(
        path: '/trainers/{id}',
        tags: ['trainers'],
        summary: 'Delete a trainer',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Trainer deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Trainer not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteTrainer() {}
}
