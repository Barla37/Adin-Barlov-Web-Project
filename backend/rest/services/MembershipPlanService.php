<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/MembershipPlanDao.php';
require_once __DIR__ . '/../dao/MembershipDao.php';

class MembershipPlanService extends BaseService
{
    protected $entity_name = 'Membership plan';
    private $membership_dao;

    /** DAOs can be passed in (used by unit tests); by default real DAOs are created. */
    public function __construct($dao = null, $membership_dao = null)
    {
        parent::__construct($dao ?? new MembershipPlanDao());
        $this->membership_dao = $membership_dao ?? new MembershipDao();
    }

    protected function normalize($plan)
    {
        $plan = $this->empty_to_null(parent::normalize($plan), ['description']);
        if (array_key_exists('includes_classes', $plan)) {
            $value = filter_var($plan['includes_classes'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) {
                throw new ValidationException("Field 'includes_classes' must be true or false.");
            }
            $plan['includes_classes'] = $value ? 1 : 0;
        }
        return $plan;
    }

    /** Rule: a plan that is used by memberships cannot be deleted. */
    public function delete($id)
    {
        $this->get_by_id($id);
        $used = $this->membership_dao->count_by_plan($id);
        if ($used > 0) {
            throw new ValidationException("This plan is used by $used membership(s) and cannot be deleted.");
        }
        $this->dao->delete($id);
    }

    protected function validate($plan, $id = null)
    {
        Validator::required($plan, ['name', 'price', 'duration_days']);
        Validator::length($plan['name'], 'name', 2, 60);
        Validator::number($plan['price'], 'price', 0, 10000);
        Validator::integer($plan['duration_days'], 'duration_days', 1, 3650);
        if (!empty($plan['description'])) {
            Validator::length($plan['description'], 'description', 0, 500);
        }
        // Rule: plan names are unique
        $existing = $this->dao->get_by_name($plan['name']);
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("A plan named '{$plan['name']}' already exists.");
        }
    }
}
