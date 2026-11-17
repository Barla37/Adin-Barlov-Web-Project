<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users', ['first_name', 'last_name', 'email', 'password_hash', 'phone', 'role']);
    }

    /** Used by login (milestone 4). */
    public function get_by_email($email)
    {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    /** Admin list without the password hash. */
    public function get_all_safe()
    {
        return $this->query(
            "SELECT id, first_name, last_name, email, phone, role, created_at FROM users ORDER BY id"
        );
    }

    public function get_by_role($role)
    {
        return $this->query("SELECT * FROM users WHERE role = :role ORDER BY last_name", ['role' => $role]);
    }

    /** Search by part of first name, last name or email. */
    public function search($term)
    {
        $like = '%' . $term . '%';
        return $this->query(
            "SELECT id, first_name, last_name, email, role FROM users
             WHERE first_name LIKE :t1 OR last_name LIKE :t2 OR email LIKE :t3
             ORDER BY last_name",
            ['t1' => $like, 't2' => $like, 't3' => $like]
        );
    }
}
