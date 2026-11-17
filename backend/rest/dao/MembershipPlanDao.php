<?php
require_once __DIR__ . '/BaseDao.php';

class MembershipPlanDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('membership_plans', ['name', 'price', 'duration_days', 'description', 'includes_classes']);
    }

    public function get_by_name($name)
    {
        return $this->query_unique("SELECT * FROM membership_plans WHERE name = :name", ['name' => $name]);
    }

    /** Plans sorted from cheapest to most expensive (pricing page). */
    public function get_all_by_price()
    {
        return $this->query("SELECT * FROM membership_plans ORDER BY price ASC");
    }

    /** Statistics for admin: how many ACTIVE memberships each plan has (plans with none show 0). */
    public function get_with_active_member_count()
    {
        return $this->query(
            "SELECT p.id, p.name, p.price, COUNT(m.id) AS active_members
             FROM membership_plans p
             LEFT JOIN memberships m ON m.plan_id = p.id AND m.status = 'active'
             GROUP BY p.id, p.name, p.price
             ORDER BY p.price ASC"
        );
    }
}
