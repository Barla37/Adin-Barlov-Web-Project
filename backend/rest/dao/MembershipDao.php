<?php
require_once __DIR__ . '/BaseDao.php';

class MembershipDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('memberships', ['user_id', 'plan_id', 'start_date', 'end_date', 'status']);
    }

    /** Admin table: every membership with member name and plan name. */
    public function get_all_with_details()
    {
        return $this->query(
            "SELECT m.id, m.user_id, u.first_name, u.last_name, m.plan_id, p.name AS plan_name,
                    m.start_date, m.end_date, m.status
             FROM memberships m
             JOIN users u ON u.id = m.user_id
             JOIN membership_plans p ON p.id = m.plan_id
             ORDER BY m.id"
        );
    }

    /** All memberships of one user, newest first. */
    public function get_by_user($user_id)
    {
        return $this->query(
            "SELECT m.*, p.name AS plan_name, p.price
             FROM memberships m
             JOIN membership_plans p ON p.id = m.plan_id
             WHERE m.user_id = :user_id
             ORDER BY m.start_date DESC",
            ['user_id' => $user_id]
        );
    }

    /** The current active membership of a user, or null. */
    public function get_active_by_user($user_id)
    {
        return $this->query_unique(
            "SELECT m.*, p.name AS plan_name
             FROM memberships m
             JOIN membership_plans p ON p.id = m.plan_id
             WHERE m.user_id = :user_id AND m.status = 'active'
             ORDER BY m.end_date DESC",
            ['user_id' => $user_id]
        );
    }

    /** Marks every 'active' membership that ended before $today as 'expired'. Returns how many. */
    public function expire_old($today)
    {
        return $this->execute(
            "UPDATE memberships SET status = 'expired' WHERE status = 'active' AND end_date < :today",
            ['today' => $today]
        );
    }
}
