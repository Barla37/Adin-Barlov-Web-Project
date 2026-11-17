<?php
/**
 * Manual DAO test. Run from the project root after importing database/gym_app.sql
 * and creating backend/config/config.php:
 *
 *     php backend/rest/dao/test.php
 *
 * Everything runs inside one transaction that is rolled back at the end,
 * so the sample data in the database is left untouched.
 */
require_once __DIR__ . '/UserDao.php';
require_once __DIR__ . '/MembershipPlanDao.php';
require_once __DIR__ . '/MembershipDao.php';
require_once __DIR__ . '/TrainerDao.php';
require_once __DIR__ . '/ClassDao.php';
require_once __DIR__ . '/BookingDao.php';

$failures = 0;
function check($label, $condition)
{
    global $failures;
    echo ($condition ? "  [ OK ] " : "  [FAIL] ") . $label . "\n";
    if (!$condition) {
        $failures++;
    }
}

$users = new UserDao();
$plans = new MembershipPlanDao();
$memberships = new MembershipDao();
$trainers = new TrainerDao();
$classes = new ClassDao();
$bookings = new BookingDao();

$users->begin_transaction();
try {
    echo "\nUsers\n";
    check('get_all returns the 6 sample users', count($users->get_all()) === 6);
    $admin = $users->get_by_email('admin@example.com');
    check('get_by_email finds the admin', $admin !== null && $admin['role'] === 'admin');
    check('password is stored as a bcrypt hash', password_verify('Admin123!', $admin['password_hash']));
    check('get_all_safe has no password_hash', !array_key_exists('password_hash', $users->get_all_safe()[0]));
    $new = $users->add([
        'first_name' => 'Test', 'last_name' => 'User', 'email' => 'test@example.com',
        'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT), 'role' => 'member',
        'id' => 999, 'not_a_column' => 'ignored',
    ]);
    check('add returns the new id (id and unknown columns are ignored)', $new['id'] !== 999 && $new['id'] > 6);
    check('update changes one row', $users->update(['phone' => '+387 61 111 222'], $new['id']) === 1);
    check('get_by_id reads the update', $users->get_by_id($new['id'])['phone'] === '+387 61 111 222');
    check('search finds the new user', count($users->search('Test')) === 1);
    check('get_by_role finds the admin', count($users->get_by_role('admin')) === 1);

    echo "\nMembership plans\n";
    check('get_all returns the 4 sample plans', count($plans->get_all()) === 4);
    check('get_all_by_price starts with the cheapest', $plans->get_all_by_price()[0]['name'] === 'Student');
    $stats = $plans->get_with_active_member_count();
    $byName = array_column($stats, 'active_members', 'name');
    check('active member counts are right (Premium 1, Basic 1, VIP 1, Student 1)',
        (int) $byName['Premium'] === 1 && (int) $byName['Basic'] === 1 && (int) $byName['VIP'] === 1 && (int) $byName['Student'] === 1);
    $plan = $plans->add(['name' => 'Test plan', 'price' => 10, 'duration_days' => 7, 'description' => 'tmp', 'includes_classes' => 0]);
    check('new plan with no members shows 0 active', (int) array_column($plans->get_with_active_member_count(), 'active_members', 'name')['Test plan'] === 0);
    check('delete removes the new plan', $plans->delete($plan['id']) === 1 && $plans->get_by_id($plan['id']) === null);

    echo "\nMemberships\n";
    check('get_all_with_details returns 7 rows with names', count($memberships->get_all_with_details()) === 7);
    check('get_by_user(2) returns 2 memberships', count($memberships->get_by_user(2)) === 2);
    check('get_active_by_user(2) is the Premium plan', $memberships->get_active_by_user(2)['plan_name'] === 'Premium');
    $m = $memberships->add(['user_id' => $new['id'], 'plan_id' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'active']);
    check('expire_old expires only the old membership', $memberships->expire_old('2026-11-17') === 1
        && $memberships->get_by_id($m['id'])['status'] === 'expired');

    echo "\nTrainers\n";
    check('get_all returns the 3 sample trainers', count($trainers->get_all()) === 3);
    check('get_by_specialty finds Yoga', count($trainers->get_by_specialty('Yoga')) === 1);
    $counts = array_column($trainers->get_with_class_count(), 'class_count', 'full_name');
    check('class counts are right (Amar 4, Lejla 3, Emir 3)',
        (int) $counts['Amar Hodžić'] === 4 && (int) $counts['Lejla Begić'] === 3 && (int) $counts['Emir Kovač'] === 3);
    $t = $trainers->add(['full_name' => 'Temp Trainer', 'specialty' => 'Boxing']);
    check('trainer with no classes can be deleted', $trainers->delete($t['id']) === 1);

    echo "\nClasses\n";
    check('get_all returns the 10 sample classes', count($classes->get_all()) === 10);
    $one = $classes->get_details_by_id(6);
    check('get_details_by_id has trainer name and 3 confirmed bookings, capacity 3',
        $one['trainer_name'] === 'Lejla Begić' && (int) $one['booked'] === 3 && (int) $one['capacity'] === 3);
    check('cancelled booking is not counted (class 8 has 0 booked)', (int) $classes->get_details_by_id(8)['booked'] === 0);
    check('get_upcoming finds classes from 2026-11-24 on', count($classes->get_upcoming('2026-11-24 00:00:00')) === 6);
    check('get_by_trainer(1) returns 4 classes', count($classes->get_by_trainer(1)) === 4);
    $c = $classes->add(['trainer_id' => 1, 'name' => 'Temp class', 'starts_at' => '2027-01-01 10:00:00', 'duration_min' => 30, 'capacity' => 5, 'room' => 'Studio A']);
    check('update changes the capacity', $classes->update(['capacity' => 8], $c['id']) === 1 && (int) $classes->get_by_id($c['id'])['capacity'] === 8);

    echo "\nBookings\n";
    check('get_all returns the 16 sample bookings', count($bookings->get_all()) === 16);
    check('get_by_user(2) returns 6 bookings with class names', count($bookings->get_by_user(2)) === 6);
    check('get_by_class(6) returns 3 attendees', count($bookings->get_by_class(6)) === 3);
    check('count_confirmed(6) is 3', $bookings->count_confirmed(6) === 3);
    check('get_by_user_and_class finds an existing booking', $bookings->get_by_user_and_class(2, 6) !== null);
    $b = $bookings->add(['user_id' => $new['id'], 'class_id' => $c['id']]);
    check('new booking defaults to confirmed', $bookings->get_by_id($b['id'])['status'] === 'confirmed');
    check('set_status cancels it', $bookings->set_status($b['id'], 'cancelled') === 1 && $bookings->count_confirmed($c['id']) === 0);

    echo "\nConstraints\n";
    $blocked = function ($label, $fn) {
        try {
            $fn();
            check($label, false);
        } catch (PDOException $e) {
            check($label, true);
        }
    };
    // Each statement that fails inside a transaction is harmless for MySQL (InnoDB keeps the transaction open).
    $blocked('duplicate email is rejected (UNIQUE)', function () use ($users) {
        $users->add(['first_name' => 'X', 'last_name' => 'Y', 'email' => 'admin@example.com', 'password_hash' => 'x']);
    });
    $blocked('booking the same class twice is rejected (UNIQUE user+class)', function () use ($bookings, $new, $c) {
        $bookings->add(['user_id' => $new['id'], 'class_id' => $c['id']]);
    });
    $blocked('booking for a missing user is rejected (FK)', function () use ($bookings) {
        $bookings->add(['user_id' => 12345, 'class_id' => 1]);
    });
    $blocked('deleting a trainer who has classes is rejected (FK RESTRICT)', function () use ($trainers) {
        $trainers->delete(1);
    });
    $blocked('negative price is rejected (CHECK, needs MySQL 8.0.16+)', function () use ($plans) {
        $plans->add(['name' => 'Bad', 'price' => -5, 'duration_days' => 30]);
    });
    $blocked('membership ending before it starts is rejected (CHECK)', function () use ($memberships) {
        $memberships->add(['user_id' => 2, 'plan_id' => 1, 'start_date' => '2026-12-01', 'end_date' => '2026-11-01']);
    });
    check('add with no valid columns throws InvalidArgumentException', (function () use ($users) {
        try {
            $users->add(['bogus' => 1]);
            return false;
        } catch (InvalidArgumentException $e) {
            return true;
        }
    })());
    check('SQL injection text is stored as plain text, not executed', (function () use ($users) {
        $evil = "x'; DROP TABLE users; --";
        $users->add(['first_name' => $evil, 'last_name' => 'Y', 'email' => 'evil@example.com', 'password_hash' => 'x']);
        return count($users->search('DROP TABLE')) === 1 && count($users->get_all()) > 6;
    })());

    echo "\nCascade\n";
    $users->delete($new['id']);
    check('deleting a user also deletes his memberships and bookings (ON DELETE CASCADE)',
        count($memberships->get_by_user($new['id'])) === 0 && count($bookings->get_by_user($new['id'])) === 0);
} finally {
    $users->rollback();
}

echo "\n" . ($failures === 0 ? "All tests passed.\n" : "$failures test(s) FAILED.\n");
exit($failures === 0 ? 0 : 1);
