<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookingDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/ClassDao.php';
require_once __DIR__ . '/../dao/MembershipDao.php';

class BookingService extends BaseService
{
    const STATUSES = ['confirmed', 'cancelled', 'attended'];

    protected $entity_name = 'Booking';
    private $user_dao;
    private $class_dao;
    private $membership_dao;

    public function __construct($dao = null, $user_dao = null, $class_dao = null, $membership_dao = null)
    {
        parent::__construct($dao ?? new BookingDao());
        $this->user_dao = $user_dao ?? new UserDao();
        $this->class_dao = $class_dao ?? new ClassDao();
        $this->membership_dao = $membership_dao ?? new MembershipDao();
    }

    public function get_by_user($user_id)
    {
        if (!$this->user_dao->get_by_id($user_id)) {
            throw new NotFoundException('User not found.');
        }
        return $this->dao->get_by_user($user_id);
    }

    public function get_by_class($class_id)
    {
        if (!$this->class_dao->get_by_id($class_id)) {
            throw new NotFoundException('Class not found.');
        }
        return $this->dao->get_by_class($class_id);
    }

    /**
     * Book a place in a class. Rules:
     * - the user and the class must exist
     * - the class must not have started
     * - the user needs an active membership whose plan includes classes
     * - a user cannot book the same class twice (a cancelled booking can be re-confirmed)
     * - the class must have a free place; checking and saving happen in one transaction
     * The status is always 'confirmed': the client cannot choose it.
     */
    public function add($booking)
    {
        $booking = $this->normalize($booking);
        Validator::required($booking, ['user_id', 'class_id']);
        Validator::integer($booking['user_id'], 'user_id', 1);
        Validator::integer($booking['class_id'], 'class_id', 1);
        $user_id = (int) $booking['user_id'];
        $class_id = (int) $booking['class_id'];

        if (!$this->user_dao->get_by_id($user_id)) {
            throw new ValidationException('The selected user does not exist.');
        }
        $class = $this->class_dao->get_by_id($class_id);
        if (!$class) {
            throw new ValidationException('The selected class does not exist.');
        }
        if ($class['starts_at'] <= date('Y-m-d H:i:s')) {
            throw new ValidationException("'{$class['name']}' has already started - booking is closed.");
        }

        $membership = $this->membership_dao->get_active_by_user($user_id);
        $today = date('Y-m-d');
        if (!$membership || $membership['start_date'] > $today || $membership['end_date'] < $today) {
            throw new ValidationException('The user needs an active membership to book a class.');
        }
        if (!$membership['includes_classes']) {
            throw new ValidationException("The {$membership['plan_name']} plan does not include group classes.");
        }

        $existing = $this->dao->get_by_user_and_class($user_id, $class_id);
        if ($existing && $existing['status'] !== 'cancelled') {
            throw new ValidationException("The user has already booked '{$class['name']}'.");
        }

        // Check capacity and save together; the count is repeated inside the transaction
        $this->dao->begin_transaction();
        try {
            if ($this->dao->count_confirmed($class_id) >= (int) $class['capacity']) {
                throw new ValidationException("'{$class['name']}' is full.");
            }
            if ($existing) {
                $this->dao->set_status($existing['id'], 'confirmed');
                $id = $existing['id'];
            } else {
                $created = $this->dao->add(['user_id' => $user_id, 'class_id' => $class_id, 'status' => 'confirmed']);
                $id = $created['id'];
            }
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollback();
            throw $e;
        }
        return $this->get_by_id($id);
    }

    /**
     * Only the status of a booking can change (the user and the class cannot be moved to other ones).
     * - 'attended' is possible only after the class has started
     * - 'cancelled' is possible only before the class starts
     * - 'confirmed' again needs a free place and a class that has not started
     */
    public function update($booking, $id)
    {
        $existing = $this->get_by_id($id);
        $booking = $this->normalize($booking);
        Validator::required($booking, ['status']);
        Validator::one_of($booking['status'], 'status', self::STATUSES);
        $status = $booking['status'];

        $class = $this->class_dao->get_by_id($existing['class_id']);
        $started = $class['starts_at'] <= date('Y-m-d H:i:s');

        if ($status === 'attended' && !$started) {
            throw new ValidationException("'{$class['name']}' has not started yet.");
        }
        if ($status === 'cancelled' && $started && $existing['status'] !== 'cancelled') {
            throw new ValidationException("'{$class['name']}' has already started - it cannot be cancelled.");
        }
        if ($status === 'confirmed' && $existing['status'] !== 'confirmed') {
            if ($started) {
                throw new ValidationException("'{$class['name']}' has already started.");
            }
            if ($this->dao->count_confirmed($class['id']) >= (int) $class['capacity']) {
                throw new ValidationException("'{$class['name']}' is full.");
            }
        }

        $this->dao->set_status($id, $status);
        return $this->get_by_id($id);
    }
}
