<?php
use OpenApi\Attributes as OA;

/*
 * General API information + reusable schemas (referenced from the paths with ref: '#/components/schemas/...').
 * swagger-php 6 reads these PHP 8 attributes - the class itself is empty.
 * (Plain /* comments on purpose: swagger-php would turn a doc comment into a description.)
 */
#[OA\Info(
    title: 'Barla Gym API',
    description: 'REST API of Barla Gym: members, membership plans, trainers, group classes and bookings. IT 2001 Web Programming, IBU.',
    version: '1.0',
    contact: new OA\Contact(name: 'Adin Barlov')
)]
#[OA\Server(url: API_BASE_URL, description: 'This server')]
#[OA\Schema(
    schema: 'Error',
    properties: [
        new OA\Property(property: 'error', type: 'string', example: 'Field \'name\' is required.')
    ]
)]
#[OA\Schema(
    schema: 'Message',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Trainer deleted')
    ]
)]
#[OA\Schema(
    schema: 'UserInput',
    required: ['first_name', 'last_name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', example: 'Ana'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Kovač'),
        new OA\Property(property: 'email', type: 'string', example: 'ana@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+387 61 000 002'),
        new OA\Property(property: 'role', type: 'string', example: 'member'),
        new OA\Property(property: 'password', type: 'string', example: 'Member123!')
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'first_name', type: 'string', example: 'Ana'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Kovač'),
        new OA\Property(property: 'email', type: 'string', example: 'ana@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+387 61 000 002'),
        new OA\Property(property: 'role', type: 'string', example: 'member'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-09-05 10:15:00')
    ]
)]
#[OA\Schema(
    schema: 'MembershipPlanInput',
    required: ['name', 'price', 'duration_days'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Premium'),
        new OA\Property(property: 'price', type: 'number', example: 50),
        new OA\Property(property: 'duration_days', type: 'integer', example: 30),
        new OA\Property(property: 'description', type: 'string', example: 'Gym floor, classes and sauna'),
        new OA\Property(property: 'includes_classes', type: 'boolean', example: true)
    ]
)]
#[OA\Schema(
    schema: 'MembershipPlan',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'name', type: 'string', example: 'Premium'),
        new OA\Property(property: 'price', type: 'number', example: 50),
        new OA\Property(property: 'duration_days', type: 'integer', example: 30),
        new OA\Property(property: 'description', type: 'string', example: 'Gym floor, classes and sauna'),
        new OA\Property(property: 'includes_classes', type: 'boolean', example: true)
    ]
)]
#[OA\Schema(
    schema: 'MembershipInput',
    required: ['user_id', 'plan_id'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'plan_id', type: 'integer', example: 2),
        new OA\Property(property: 'start_date', type: 'string', example: '2026-12-01'),
        new OA\Property(property: 'end_date', type: 'string', example: '2026-12-31'),
        new OA\Property(property: 'status', type: 'string', example: 'active')
    ]
)]
#[OA\Schema(
    schema: 'Membership',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'plan_id', type: 'integer', example: 2),
        new OA\Property(property: 'start_date', type: 'string', example: '2026-12-01'),
        new OA\Property(property: 'end_date', type: 'string', example: '2026-12-31'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'first_name', type: 'string', example: 'Ana'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Kovač'),
        new OA\Property(property: 'plan_name', type: 'string', example: 'Premium')
    ]
)]
#[OA\Schema(
    schema: 'TrainerInput',
    required: ['full_name', 'specialty'],
    properties: [
        new OA\Property(property: 'full_name', type: 'string', example: 'Amar Hodžić'),
        new OA\Property(property: 'specialty', type: 'string', example: 'HIIT, Strength'),
        new OA\Property(property: 'bio', type: 'string', example: '8 years of experience.')
    ]
)]
#[OA\Schema(
    schema: 'Trainer',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'full_name', type: 'string', example: 'Amar Hodžić'),
        new OA\Property(property: 'specialty', type: 'string', example: 'HIIT, Strength'),
        new OA\Property(property: 'bio', type: 'string', example: '8 years of experience.'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-09-01 09:00:00')
    ]
)]
#[OA\Schema(
    schema: 'GymClassInput',
    required: ['trainer_id', 'name', 'starts_at', 'duration_min', 'capacity', 'room'],
    properties: [
        new OA\Property(property: 'trainer_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'HIIT Blast'),
        new OA\Property(property: 'description', type: 'string', example: 'High intensity interval training.'),
        new OA\Property(property: 'starts_at', type: 'string', example: '2026-12-10 18:00'),
        new OA\Property(property: 'duration_min', type: 'integer', example: 45),
        new OA\Property(property: 'capacity', type: 'integer', example: 20),
        new OA\Property(property: 'room', type: 'string', example: 'Studio A')
    ]
)]
#[OA\Schema(
    schema: 'GymClass',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 5),
        new OA\Property(property: 'trainer_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'HIIT Blast'),
        new OA\Property(property: 'description', type: 'string', example: 'High intensity interval training.'),
        new OA\Property(property: 'starts_at', type: 'string', example: '2026-12-10 18:00'),
        new OA\Property(property: 'duration_min', type: 'integer', example: 45),
        new OA\Property(property: 'capacity', type: 'integer', example: 20),
        new OA\Property(property: 'room', type: 'string', example: 'Studio A'),
        new OA\Property(property: 'trainer_name', type: 'string', example: 'Amar Hodžić'),
        new OA\Property(property: 'booked', type: 'integer', example: 2)
    ]
)]
#[OA\Schema(
    schema: 'BookingInput',
    required: ['user_id', 'class_id'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'class_id', type: 'integer', example: 5)
    ]
)]
#[OA\Schema(
    schema: 'Booking',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 8),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'class_id', type: 'integer', example: 5),
        new OA\Property(property: 'status', type: 'string', example: 'confirmed'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-11-10 09:00:00')
    ]
)]
#[OA\Schema(
    schema: 'BookingStatus',
    required: ['status'],
    properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['confirmed', 'cancelled', 'attended'], example: 'cancelled')
    ]
)]
#[OA\Schema(
    schema: 'Exercise',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 345),
        new OA\Property(property: 'name', type: 'string', example: 'Bench Press'),
        new OA\Property(property: 'description', type: 'string', example: 'Lie on a bench and press the bar up.'),
        new OA\Property(property: 'category', type: 'string', example: 'Chest'),
        new OA\Property(property: 'muscles', type: 'array', items: new OA\Items(type: 'string'), example: ['Pectoralis major']),
        new OA\Property(property: 'equipment', type: 'array', items: new OA\Items(type: 'string'), example: ['Barbell', 'Bench'])
    ]
)]
#[OA\Schema(
    schema: 'ExercisePage',
    properties: [
        new OA\Property(property: 'count', type: 'integer', example: 400, description: 'Total number of exercises at wger'),
        new OA\Property(property: 'limit', type: 'integer', example: 12),
        new OA\Property(property: 'offset', type: 'integer', example: 0),
        new OA\Property(property: 'results', type: 'array', items: new OA\Items(ref: '#/components/schemas/Exercise'))
    ]
)]
class OpenApiSpec {}
