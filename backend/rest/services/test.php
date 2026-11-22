<?php
/**
 * Tries to break every business rule of the services. Run from the backend folder after importing
 * database/gym_app.sql and creating config/config.php:
 *
 *     php rest/services/test.php
 *
 * Every line shows what was tried and what happened. Lines marked [ OK ] behaved as expected,
 * [FAIL] means a rule did not work. Everything the test creates is deleted again at the end,
 * so it can be run as often as you like. (Services open their own transactions, so this script
 * cleans up by deleting instead of using one big transaction.)
 */
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/MembershipPlanService.php';
require_once __DIR__ . '/MembershipService.php';
require_once __DIR__ . '/TrainerService.php';
require_once __DIR__ . '/ClassService.php';
require_once __DIR__ . '/BookingService.php';
require_once __DIR__ . '/ExerciseService.php';

$failures = 0;

/** The action must work; returns its result. */
function works($description, $action)
{
    global $failures;
    echo str_pad($description, 62, '.') . ' ';
    try {
        $result = $action();
        echo "[ OK ]" . (is_array($result) && isset($result['id']) ? " (id {$result['id']})" : '') . "\n";
        return $result;
    } catch (Throwable $e) {
        $failures++;
        echo "[FAIL] unexpected " . get_class($e) . ": " . $e->getMessage() . "\n";
        return null;
    }
}

/** The action must be refused with this exception class and HTTP status. */
function refused($description, $class, $status, $action)
{
    global $failures;
    echo str_pad($description, 62, '.') . ' ';
    try {
        $action();
        $failures++;
        echo "[FAIL] no exception was thrown\n";
    } catch (HttpException $e) {
        if ($e instanceof $class && $e->get_status() === $status) {
            echo "[ OK ] " . get_class($e) . " [$status]: " . $e->getMessage() . "\n";
        } else {
            $failures++;
            echo "[FAIL] " . get_class($e) . " [{$e->get_status()}]: " . $e->getMessage() . "\n";
        }
    } catch (Throwable $e) {
        $failures++;
        echo "[FAIL] " . get_class($e) . ": " . $e->getMessage() . "\n";
    }
}

$users = new UserService();
$plans = new MembershipPlanService();
$memberships = new MembershipService();
$trainers = new TrainerService();
$classes = new ClassService();
$bookings = new BookingService();
$exercises = new ExerciseService();

$user_dao = new UserDao();
$class_dao = new ClassDao();
$plan_dao = new MembershipPlanDao();
$trainer_dao = new TrainerDao();

$made = ['users' => [], 'classes' => [], 'plans' => [], 'trainers' => []];
$run = substr(md5((string) microtime(true)), 0, 6);       // makes test e-mails unique
$next_week = date('Y-m-d H:i', strtotime('+7 days 10:00'));
$today = date('Y-m-d');

try {
    echo "\n== Users ==\n";
    refused('User without e-mail', ValidationException::class, 400, fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'password' => 'Secret123']));
    refused('User with an invalid e-mail', ValidationException::class, 400, fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'not-an-email', 'password' => 'Secret123']));
    refused('Duplicate e-mail (different letter case)', ValidationException::class, 400, fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'ADMIN@example.com', 'password' => 'Secret123']));
    refused('Weak password', ValidationException::class, 400, fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'email' => "weak.$run@example.com", 'password' => 'abc']));
    refused('Unknown role', ValidationException::class, 400, fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'email' => "role.$run@example.com", 'password' => 'Secret123', 'role' => 'boss']));
    refused('Get user #99999', NotFoundException::class, 404, fn() => $users->get_by_id(99999));
    $a = works('Add a valid user', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'Alpha', 'email' => "Alpha.$run@Example.com", 'password' => 'Secret123', 'password_hash' => 'hacked']));
    $made['users'][] = $a['id'];
    echo str_pad('Result has no password hash, e-mail is lower case', 62, '.') . ' ';
    $ok = $a && !isset($a['password_hash']) && $a['email'] === "alpha.$run@example.com";
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    echo str_pad('Password is stored as a bcrypt hash (the client hash is ignored)', 62, '.') . ' ';
    $stored = $user_dao->get_by_email("alpha.$run@example.com");
    $ok = $stored && password_verify('Secret123', $stored['password_hash']);
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    works('Change only the phone number', fn() => $users->update(['phone' => '+387 61 123 456'], $a['id']));
    works('Change the password', fn() => $users->update(['password' => 'NewSecret456'], $a['id']));
    echo str_pad('New password works, old one does not', 62, '.') . ' ';
    $stored = $user_dao->get_by_email("alpha.$run@example.com");
    $ok = password_verify('NewSecret456', $stored['password_hash']) && !password_verify('Secret123', $stored['password_hash']);
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    refused('Update with an e-mail that belongs to another user', ValidationException::class, 400, fn() => $users->update(['email' => 'admin@example.com'], $a['id']));
    refused('Delete the last admin', ValidationException::class, 400, fn() => $users->delete(1));
    refused('Remove the admin role from the last admin', ValidationException::class, 400, fn() => $users->update(['role' => 'member'], 1));

    echo "\n== Membership plans ==\n";
    refused('Plan without a name', ValidationException::class, 400, fn() => $plans->add(['price' => 10, 'duration_days' => 30]));
    refused('Duplicate plan name', ValidationException::class, 400, fn() => $plans->add(['name' => 'Premium', 'price' => 10, 'duration_days' => 30]));
    refused('Negative price', ValidationException::class, 400, fn() => $plans->add(['name' => "Neg $run", 'price' => -5, 'duration_days' => 30]));
    refused('Duration 0 days', ValidationException::class, 400, fn() => $plans->add(['name' => "Zero $run", 'price' => 5, 'duration_days' => 0]));
    refused('includes_classes = "maybe"', ValidationException::class, 400, fn() => $plans->add(['name' => "Maybe $run", 'price' => 5, 'duration_days' => 30, 'includes_classes' => 'maybe']));
    refused('Delete the Premium plan (it is in use)', ValidationException::class, 400, fn() => $plans->delete(2));
    $plan = works('Add a valid plan', fn() => $plans->add(['name' => "Test plan $run", 'price' => '19.90', 'duration_days' => 14, 'includes_classes' => true]));
    $made['plans'][] = $plan['id'];
    works('Update keeps its own name (no false duplicate)', fn() => $plans->update(['price' => 21], $plan['id']));

    echo "\n== Trainers ==\n";
    refused('Trainer with a one-letter name', ValidationException::class, 400, fn() => $trainers->add(['full_name' => 'A', 'specialty' => 'Yoga']));
    refused('Delete a trainer who has classes', ValidationException::class, 400, fn() => $trainers->delete(1));
    $trainer = works('Add a valid trainer', fn() => $trainers->add(['full_name' => "Test Trainer $run", 'specialty' => 'Boxing', 'bio' => '']));
    $made['trainers'][] = $trainer['id'];

    echo "\n== Classes ==\n";
    refused('Class in the past', ValidationException::class, 400, fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => 'Old class', 'starts_at' => '2020-01-01 10:00', 'duration_min' => 45, 'capacity' => 10, 'room' => "Room $run"]));
    refused('Class with an unknown trainer', ValidationException::class, 400, fn() => $classes->add(['trainer_id' => 99999, 'name' => 'Ghost class', 'starts_at' => $next_week, 'duration_min' => 45, 'capacity' => 10, 'room' => "Room $run"]));
    refused('Class lasting 5 minutes', ValidationException::class, 400, fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => 'Short', 'starts_at' => $next_week, 'duration_min' => 5, 'capacity' => 10, 'room' => "Room $run"]));
    refused('Class with capacity 0', ValidationException::class, 400, fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => 'Empty', 'starts_at' => $next_week, 'duration_min' => 45, 'capacity' => 0, 'room' => "Room $run"]));
    $small = works('Add a valid class (capacity 1)', fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => "Small class $run", 'starts_at' => $next_week, 'duration_min' => 60, 'capacity' => 1, 'room' => "Room $run"]));
    $made['classes'][] = $small['id'];
    refused('Another class in the same room at an overlapping time', ValidationException::class, 400, fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => 'Clash', 'starts_at' => date('Y-m-d H:i', strtotime($next_week) + 1800), 'duration_min' => 60, 'capacity' => 5, 'room' => "Room $run"]));
    $big = works('Same room right after the first class is fine', fn() => $classes->add(['trainer_id' => $trainer['id'], 'name' => "Big class $run", 'starts_at' => date('Y-m-d H:i', strtotime($next_week) + 3600), 'duration_min' => 60, 'capacity' => 2, 'room' => "Room $run"]));
    $made['classes'][] = $big['id'];
    refused('Delete the trainer who now has classes', ValidationException::class, 400, fn() => $trainers->delete($trainer['id']));

    echo "\n== Memberships ==\n";
    $b = works('Add test user Beta', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'Beta', 'email' => "beta.$run@example.com", 'password' => 'Secret123']));
    $c = works('Add test user Gamma', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'Gamma', 'email' => "gamma.$run@example.com", 'password' => 'Secret123']));
    $d = works('Add test user Delta', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'Delta', 'email' => "delta.$run@example.com", 'password' => 'Secret123']));
    array_push($made['users'], $b['id'], $c['id'], $d['id']);
    refused('Membership ending before it starts', ValidationException::class, 400, fn() => $memberships->add(['user_id' => $b['id'], 'plan_id' => 2, 'start_date' => '2026-12-10', 'end_date' => '2026-12-01']));
    refused('Membership for an unknown user', ValidationException::class, 400, fn() => $memberships->add(['user_id' => 99999, 'plan_id' => 2]));
    refused('Membership with an impossible date (2026-02-30)', ValidationException::class, 400, fn() => $memberships->add(['user_id' => $b['id'], 'plan_id' => 2, 'start_date' => '2026-02-30', 'end_date' => '2026-03-30']));
    $m = works('Premium membership; end date is calculated (start + 30 days)', fn() => $memberships->add(['user_id' => $b['id'], 'plan_id' => 2]));
    echo str_pad('End date = today + 30 days', 62, '.') . ' ';
    $ok = $m && $m['status'] === 'active' && $m['start_date'] === $today && $m['end_date'] === date('Y-m-d', strtotime('+30 days'));
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    refused('Second active membership in the same period', ValidationException::class, 400, fn() => $memberships->add(['user_id' => $b['id'], 'plan_id' => 3]));
    works('A cancelled membership in the same period is allowed', fn() => $memberships->add(['user_id' => $b['id'], 'plan_id' => 3, 'status' => 'cancelled']));
    works('Premium membership for Gamma', fn() => $memberships->add(['user_id' => $c['id'], 'plan_id' => 2]));
    works('Basic membership (no classes) for Delta', fn() => $memberships->add(['user_id' => $d['id'], 'plan_id' => 1]));
    refused('Memberships of user #99999', NotFoundException::class, 404, fn() => $memberships->get_by_user(99999));

    echo "\n== Bookings ==\n";
    $e = works('Add test user Epsilon (no membership)', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'Epsilon', 'email' => "eps.$run@example.com", 'password' => 'Secret123']));
    $made['users'][] = $e['id'];
    refused('User without a membership books a class', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $e['id'], 'class_id' => $small['id']]));
    refused('Basic plan does not include classes', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $d['id'], 'class_id' => $small['id']]));
    refused('Booking an unknown class', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => 99999]));
    refused('Booking a class that already started (sample class #1)', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => 1]));
    $bk = works('Beta books the small class', fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => $small['id'], 'status' => 'attended']));
    echo str_pad('Status is always confirmed (client value ignored)', 62, '.') . ' ';
    $ok = $bk && $bk['status'] === 'confirmed';
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    refused('Beta books the same class twice', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => $small['id']]));
    refused('Gamma books the full class (capacity 1)', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $c['id'], 'class_id' => $small['id']]));
    refused('Mark a future class as attended', ValidationException::class, 400, fn() => $bookings->update(['status' => 'attended'], $bk['id']));
    refused('Unknown booking status', ValidationException::class, 400, fn() => $bookings->update(['status' => 'maybe'], $bk['id']));
    works('Beta cancels', fn() => $bookings->update(['status' => 'cancelled'], $bk['id']));
    $gk = works('Gamma can now book the freed place', fn() => $bookings->add(['user_id' => $c['id'], 'class_id' => $small['id']]));
    refused('Beta re-confirms but the class is full again', ValidationException::class, 400, fn() => $bookings->update(['status' => 'confirmed'], $bk['id']));
    refused('Beta books again (cancelled booking, class full)', ValidationException::class, 400, fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => $small['id']]));
    works('Gamma cancels', fn() => $bookings->update(['status' => 'cancelled'], $gk['id']));
    $again = works('Beta books again: the cancelled booking is re-confirmed', fn() => $bookings->add(['user_id' => $b['id'], 'class_id' => $small['id']]));
    echo str_pad('Same booking id is reused', 62, '.') . ' ';
    $ok = $again && $again['id'] === $bk['id'] && $again['status'] === 'confirmed';
    echo $ok ? "[ OK ]\n" : "[FAIL]\n";
    $failures += $ok ? 0 : 1;
    refused('Delete a class that has bookings', ValidationException::class, 400, fn() => $classes->delete($small['id']));
    works('Beta and Gamma book the big class (capacity 2)', function () use ($bookings, $b, $c, $big) {
        $bookings->add(['user_id' => $b['id'], 'class_id' => $big['id']]);
        return $bookings->add(['user_id' => $c['id'], 'class_id' => $big['id']]);
    });
    refused('Lower the capacity of the big class to 1', ValidationException::class, 400, fn() => $classes->update(['capacity' => 1], $big['id']));
    works('Class list shows trainer name and booked places', function () use ($classes, $big) {
        $row = $classes->get_by_id($big['id']);
        if ($row['trainer_name'] === null || (int) $row['booked'] !== 2) {
            throw new Exception('wrong details: ' . json_encode($row));
        }
        return $row;
    });
    refused('Bookings of class #99999', NotFoundException::class, 404, fn() => $bookings->get_by_class(99999));

    echo "\n== Exercises (external wger API) ==\n";
    $ex = new ExerciseService();
    refused('Limit 0', ValidationException::class, 400, fn() => $ex->get_exercises(0, 0));
    refused('Limit 500', ValidationException::class, 400, fn() => $ex->get_exercises(500, 0));
    refused('Offset -1', ValidationException::class, 400, fn() => $ex->get_exercises(5, -1));
    echo str_pad('Real call to wger (needs internet; 503 is also a correct answer)', 62, '.') . ' ';
    try {
        $page = $ex->get_exercises(3, 0);
        echo "[ OK ] got " . count($page['results']) . " of {$page['count']} exercises, first: " . ($page['results'][0]['name'] ?? '-') . "\n";
    } catch (ExternalServiceException $e) {
        echo "[ OK ] ExternalServiceException [503]: {$e->getMessage()} (see backend/logs/error.log)\n";
    }
} finally {
    // Delete everything this test created (users first: their memberships and bookings are deleted with them)
    foreach ($made['users'] as $id) {
        $user_dao->delete($id);
    }
    foreach ($made['classes'] as $id) {
        $class_dao->delete($id);
    }
    foreach ($made['plans'] as $id) {
        $plan_dao->delete($id);
    }
    foreach ($made['trainers'] as $id) {
        $trainer_dao->delete($id);
    }
}

echo "\n" . ($failures === 0 ? "All service checks passed.\n" : "$failures check(s) FAILED.\n");
exit($failures === 0 ? 0 : 1);
