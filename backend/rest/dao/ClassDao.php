<?php
require_once __DIR__ . '/BaseDao.php';

class ClassDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('classes', ['trainer_id', 'name', 'description', 'starts_at', 'duration_min', 'capacity', 'room']);
    }

    /** Schedule page: classes with trainer name and number of confirmed bookings. */
    public function get_all_with_details()
    {
        return $this->query(
            "SELECT c.id, c.name, c.description, c.starts_at, c.duration_min, c.capacity, c.room,
                    c.trainer_id, t.full_name AS trainer_name,
                    COUNT(b.id) AS booked
             FROM classes c
             JOIN trainers t ON t.id = c.trainer_id
             LEFT JOIN bookings b ON b.class_id = c.id AND b.status = 'confirmed'
             GROUP BY c.id, c.name, c.description, c.starts_at, c.duration_min, c.capacity, c.room,
                      c.trainer_id, t.full_name
             ORDER BY c.starts_at"
        );
    }

    /** One class with trainer name and booking count, or null. */
    public function get_details_by_id($id)
    {
        return $this->query_unique(
            "SELECT c.id, c.name, c.description, c.starts_at, c.duration_min, c.capacity, c.room,
                    c.trainer_id, t.full_name AS trainer_name,
                    COUNT(b.id) AS booked
             FROM classes c
             JOIN trainers t ON t.id = c.trainer_id
             LEFT JOIN bookings b ON b.class_id = c.id AND b.status = 'confirmed'
             WHERE c.id = :id
             GROUP BY c.id, c.name, c.description, c.starts_at, c.duration_min, c.capacity, c.room,
                      c.trainer_id, t.full_name",
            ['id' => $id]
        );
    }

    /** Classes that start at or after the given moment (format 'Y-m-d H:i:s'). */
    public function get_upcoming($from)
    {
        return $this->query(
            "SELECT * FROM classes WHERE starts_at >= :from ORDER BY starts_at",
            ['from' => $from]
        );
    }

    public function get_by_trainer($trainer_id)
    {
        return $this->query(
            "SELECT * FROM classes WHERE trainer_id = :trainer_id ORDER BY starts_at",
            ['trainer_id' => $trainer_id]
        );
    }
}
