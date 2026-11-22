<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/MembershipPlanRoutes.php (one empty method per endpoint).
 * The methods are empty - they only carry the #[OA\...] attributes. Keep them in sync with the routes.
 */
class MembershipPlanPaths
{
    #[OA\Get(
        path: '/membership-plans',
        tags: ['membership-plans'],
        summary: 'List all membership plans',
        responses: [
            new OA\Response(response: 200, description: 'List of membership plans', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/MembershipPlan')))
        ]
    )]
    public function getMembershipPlans() {}

    #[OA\Get(
        path: '/membership-plans/{id}',
        tags: ['membership-plans'],
        summary: 'Get one membership plan',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The membership plan', content: new OA\JsonContent(ref: '#/components/schemas/MembershipPlan')),
            new OA\Response(response: 404, description: 'Membership plan not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getMembershipPlanById() {}

    #[OA\Post(
        path: '/membership-plans',
        tags: ['membership-plans'],
        summary: 'Create a membership plan',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MembershipPlanInput')),
        responses: [
            new OA\Response(response: 201, description: 'Created membership plan', content: new OA\JsonContent(ref: '#/components/schemas/MembershipPlan')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postMembershipPlan() {}

    #[OA\Put(
        path: '/membership-plans/{id}',
        tags: ['membership-plans'],
        summary: 'Update a membership plan (only the sent fields change; PATCH works the same)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MembershipPlanInput')),
        responses: [
            new OA\Response(response: 200, description: 'Updated membership plan', content: new OA\JsonContent(ref: '#/components/schemas/MembershipPlan')),
            new OA\Response(response: 400, description: 'Invalid data or a business rule is broken', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Membership plan not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putMembershipPlan() {}

    #[OA\Delete(
        path: '/membership-plans/{id}',
        tags: ['membership-plans'],
        summary: 'Delete a membership plan',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Membership plan deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'A business rule forbids the deletion', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Membership plan not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteMembershipPlan() {}
}
