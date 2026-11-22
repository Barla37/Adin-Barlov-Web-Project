<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/MembershipDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/MembershipPlanDao.php';

class MembershipService extends BaseService
{
    const STATUSES = ['active', 'expired', 'cancelled'];

    protected $entity_name = 'Membership';
    private $user_dao;
    private $plan_dao;

    public function __construct($dao = null, $user_dao = null, $plan_dao = null)
    {
        parent::__construct($dao ?? new MembershipDao());
        $this->user_dao = $user_dao ?? new UserDao();
        $this->plan_dao = $plan_dao ?? new MembershipPlanDao();
    }

    /** The admin table shows the member and plan names, so we return those. */
    public function get_all()
    {
        return $this->dao->get_all_with_details();
    }

    public function get_by_id($id)
    {
        $membership = $this->dao->get_details_by_id($id);
        if (!$membership) {
            throw new NotFoundException("{$this->entity_name} not found.");
        }
        return $membership;
    }

    /** All memberships of one user. */
    public function get_by_user($user_id)
    {
        if (!$this->user_dao->get_by_id($user_id)) {
            throw new NotFoundException('User not found.');
        }
        return $this->dao->get_by_user($user_id);
    }

    public function add($membership)
    {
        $membership = $this->normalize($membership);
        // Defaults: active, starting today, ending after the duration of the plan
        $membership['status'] = $membership['status'] ?? 'active';
        $membership['start_date'] = $membership['start_date'] ?? date('Y-m-d');
        if (empty($membership['end_date']) && !empty($membership['plan_id']) && ctype_digit((string) $membership['plan_id'])) {
            $plan = $this->plan_dao->get_by_id($membership['plan_id']);
            if ($plan && preg_match('/^\d{4}-\d{2}-\d{2}$/', $membership['start_date'])) {
                $membership['end_date'] = date('Y-m-d', strtotime($membership['start_date'] . ' +' . (int) $plan['duration_days'] . ' days'));
            }
        }
        $this->validate($membership);
        $created = $this->dao->add($membership);
        return $this->get_by_id($created['id']);
    }

    protected function validate($membership, $id = null)
    {
        Validator::required($membership, ['user_id', 'plan_id', 'start_date', 'end_date', 'status']);
        Validator::integer($membership['user_id'], 'user_id', 1);
        Validator::integer($membership['plan_id'], 'plan_id', 1);
        Validator::date($membership['start_date'], 'start_date');
        Validator::date($membership['end_date'], 'end_date');
        Validator::one_of($membership['status'], 'status', self::STATUSES);

        if ($membership['end_date'] < $membership['start_date']) {
            throw new ValidationException("The end date cannot be before the start date.");
        }
        if (!$this->user_dao->get_by_id($membership['user_id'])) {
            throw new ValidationException('The selected user does not exist.');
        }
        if (!$this->plan_dao->get_by_id($membership['plan_id'])) {
            throw new ValidationException('The selected plan does not exist.');
        }

        // Rule: a user cannot have two ACTIVE memberships whose dates overlap
        if ($membership['status'] === 'active') {
            foreach ($this->dao->get_by_user($membership['user_id']) as $other) {
                if ($other['id'] == $id || $other['status'] !== 'active') {
                    continue;
                }
                if ($membership['start_date'] <= $other['end_date'] && $other['start_date'] <= $membership['end_date']) {
                    throw new ValidationException("This user already has an active membership ({$other['plan_name']}, {$other['start_date']} to {$other['end_date']}) in that period.");
                }
            }
        }
    }
}
