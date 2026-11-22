<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ClassDao.php';
require_once __DIR__ . '/../dao/TrainerDao.php';
require_once __DIR__ . '/../dao/BookingDao.php';

class ClassService extends BaseService
{
    protected $entity_name = 'Class';
    private $trainer_dao;
    private $booking_dao;

    public function __construct($dao = null, $trainer_dao = null, $booking_dao = null)
    {
        parent::__construct($dao ?? new ClassDao());
        $this->trainer_dao = $trainer_dao ?? new TrainerDao();
        $this->booking_dao = $booking_dao ?? new BookingDao();
    }

    /** The schedule shows the trainer name and the number of confirmed bookings, so we return those. */
    public function get_all()
    {
        return $this->dao->get_all_with_details();
    }

    public function get_by_id($id)
    {
        $class = $this->dao->get_details_by_id($id);
        if (!$class) {
            throw new NotFoundException("{$this->entity_name} not found.");
        }
        return $class;
    }

    protected function normalize($class)
    {
        $class = $this->empty_to_null(parent::normalize($class), ['description']);
        if (isset($class['starts_at'])) {
            $class['starts_at'] = Validator::normalize_datetime($class['starts_at']);
        }
        return $class;
    }

    public function add($class)
    {
        $class = $this->normalize($class);
        $this->validate($class);
        // Rule: a NEW class must be in the future (an old class may keep its date when it is edited)
        Validator::in_future($class['starts_at'], 'starts_at');
        $created = $this->dao->add($class);
        return $this->get_by_id($created['id']);
    }

    /** Rule: a class that has bookings cannot be deleted (cancel the bookings first). */
    public function delete($id)
    {
        $this->get_by_id($id);
        $bookings = count($this->booking_dao->get_by_class($id));
        if ($bookings > 0) {
            throw new ValidationException("This class has $bookings booking(s) and cannot be deleted.");
        }
        $this->dao->delete($id);
    }

    protected function validate($class, $id = null)
    {
        Validator::required($class, ['trainer_id', 'name', 'starts_at', 'duration_min', 'capacity', 'room']);
        Validator::integer($class['trainer_id'], 'trainer_id', 1);
        Validator::length($class['name'], 'name', 2, 120);
        Validator::length($class['room'], 'room', 1, 60);
        Validator::datetime($class['starts_at'], 'starts_at');
        Validator::integer($class['duration_min'], 'duration_min', 10, 300);
        Validator::integer($class['capacity'], 'capacity', 1, 200);
        if (!empty($class['description'])) {
            Validator::length($class['description'], 'description', 0, 500);
        }

        // Rule: the trainer must exist
        if (!$this->trainer_dao->get_by_id($class['trainer_id'])) {
            throw new ValidationException('The selected trainer does not exist.');
        }

        // Rule: two classes cannot use the same room at the same time
        $start = strtotime($class['starts_at']);
        $end = $start + (int) $class['duration_min'] * 60;
        foreach ($this->dao->get_by_room($class['room']) as $other) {
            if ($other['id'] == $id) {
                continue;
            }
            $other_start = strtotime($other['starts_at']);
            $other_end = $other_start + (int) $other['duration_min'] * 60;
            if ($start < $other_end && $other_start < $end) {
                throw new ValidationException("The room '{$class['room']}' is already used by '{$other['name']}' at that time.");
            }
        }

        // Rule: when editing, the capacity cannot drop below the places that are already taken
        if ($id !== null) {
            $taken = $this->booking_dao->count_confirmed($id);
            if ((int) $class['capacity'] < $taken) {
                throw new ValidationException("$taken places are already booked - the capacity cannot be lower than that.");
            }
        }
    }
}
