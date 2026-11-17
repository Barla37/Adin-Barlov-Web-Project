<?php
require_once __DIR__ . '/BaseDao.php';

class BookingDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('bookings', ['user_id', 'class_id', 'status']);
    }

    /** "My bookings" page: bookings of one user with class and trainer info. */
    public function get_by_user($user_id)
    {
        return $this->query(
            "SELECT b.id, b.status, b.created_at, c.id AS class_id, c.name AS class_name,
                    c.starts_at, c.room, t.full_name AS trainer_name
             FROM bookings b
             JOIN classes c ON c.id = b.class_id
             JOIN trainers t ON t.id = c.trainer_id
             WHERE b.user_id = :user_id
             ORDER BY c.starts_at DESC",
            ['user_id' => $user_id]
        );
    }

    /** Attendee list of a class. */
    public function get_by_class($class_id)
    {
        return $this->query(
            "SELECT b.id, b.status, b.created_at, u.id AS user_id, u.first_name, u.last_name, u.email
             FROM bookings b
             JOIN users u ON u.id = b.user_id
             WHERE b.class_id = :class_id
             ORDER BY b.created_at",
            ['class_id' => $class_id]
        );
    }

    /** The booking of this user for this class (any status), or null. Used to stop double booking. */
    public function get_by_user_and_class($user_id, $class_id)
    {
        return $this->query_unique(
            "SELECT * FROM bookings WHERE user_id = :user_id AND class_id = :class_id",
            ['user_id' => $user_id, 'class_id' => $class_id]
        );
    }

    /** Number of confirmed bookings of a class (compare with capacity). */
    public function count_confirmed($class_id)
    {
        $row = $this->query_unique(
            "SELECT COUNT(*) AS total FROM bookings WHERE class_id = :class_id AND status = 'confirmed'",
            ['class_id' => $class_id]
        );
        return (int) $row['total'];
    }

    public function set_status($id, $status)
    {
        return $this->update(['status' => $status], $id);
    }
}
