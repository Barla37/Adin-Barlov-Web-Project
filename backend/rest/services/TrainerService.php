<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/TrainerDao.php';
require_once __DIR__ . '/../dao/ClassDao.php';

class TrainerService extends BaseService
{
    protected $entity_name = 'Trainer';
    private $class_dao;

    public function __construct($dao = null, $class_dao = null)
    {
        parent::__construct($dao ?? new TrainerDao());
        $this->class_dao = $class_dao ?? new ClassDao();
    }

    protected function normalize($trainer)
    {
        return $this->empty_to_null(parent::normalize($trainer), ['bio']);
    }

    /** Rule: a trainer who still has classes cannot be deleted. */
    public function delete($id)
    {
        $this->get_by_id($id);
        $classes = count($this->class_dao->get_by_trainer($id));
        if ($classes > 0) {
            throw new ValidationException("This trainer still teaches $classes class(es). Move or delete them first.");
        }
        $this->dao->delete($id);
    }

    protected function validate($trainer, $id = null)
    {
        Validator::required($trainer, ['full_name', 'specialty']);
        Validator::length($trainer['full_name'], 'full_name', 2, 120);
        Validator::length($trainer['specialty'], 'specialty', 2, 120);
        if (!empty($trainer['bio'])) {
            Validator::length($trainer['bio'], 'bio', 0, 1000);
        }
    }
}
