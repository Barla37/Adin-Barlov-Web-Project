<?php
require_once __DIR__ . '/BaseDao.php';

class TrainerDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('trainers', ['full_name', 'specialty', 'bio']);
    }

    public function get_by_specialty($term)
    {
        return $this->query(
            "SELECT * FROM trainers WHERE specialty LIKE :term ORDER BY full_name",
            ['term' => '%' . $term . '%']
        );
    }

    /** Trainers with the number of classes they teach (trainers with no classes show 0). */
    public function get_with_class_count()
    {
        return $this->query(
            "SELECT t.id, t.full_name, t.specialty, COUNT(c.id) AS class_count
             FROM trainers t
             LEFT JOIN classes c ON c.trainer_id = t.id
             GROUP BY t.id, t.full_name, t.specialty
             ORDER BY t.full_name"
        );
    }
}
