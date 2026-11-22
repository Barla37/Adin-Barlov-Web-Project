<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    const ROLES = ['admin', 'member'];

    protected $entity_name = 'User';

    public function __construct($dao = null)
    {
        parent::__construct($dao ?? new UserDao());
    }

    protected function normalize($user)
    {
        $user = $this->empty_to_null(parent::normalize($user), ['phone']);
        if (isset($user['email'])) {
            $user['email'] = strtolower($user['email']);        // Ema@x.ba and ema@x.ba are the same account
        }
        // The client can never write the hash or the creation time directly
        unset($user['password_hash'], $user['created_at']);
        return $user;
    }

    public function add($user)
    {
        $user = $this->normalize($user);
        $user['role'] = $user['role'] ?? 'member';
        $this->validate($user);
        Validator::required($user, ['password']);
        Validator::password($user['password']);                  // check BEFORE hashing (a hash always looks valid)

        // Rule: passwords are NEVER stored in plain text
        $user['password_hash'] = password_hash($user['password'], PASSWORD_BCRYPT);
        unset($user['password']);

        $created = $this->dao->add($user);
        return $this->get_by_id($created['id']);                 // get_by_id does not return the hash
    }

    public function update($user, $id)
    {
        if (!is_array($user)) {
            throw new ValidationException('The request body must be a JSON object.');
        }
        $hash = null;
        if (isset($user['password']) && $user['password'] !== '') {
            Validator::password($user['password']);
            $hash = password_hash($user['password'], PASSWORD_BCRYPT);
        }
        unset($user['password']);

        $existing = $this->get_by_id($id);
        $new_role = $user['role'] ?? $existing['role'];
        if ($existing['role'] === 'admin' && $new_role !== 'admin' && $this->count_admins() <= 1) {
            throw new ValidationException('The last admin cannot lose the admin role.');
        }

        $updated = parent::update($user, $id);
        if ($hash !== null) {
            $this->dao->update(['password_hash' => $hash], $id);
        }
        return $updated;
    }

    /** Rule: the last admin cannot be deleted. */
    public function delete($id)
    {
        $user = $this->get_by_id($id);
        if ($user['role'] === 'admin' && $this->count_admins() <= 1) {
            throw new ValidationException('The last admin cannot be deleted.');
        }
        $this->dao->delete($id);
    }

    private function count_admins()
    {
        return count($this->dao->get_by_role('admin'));
    }

    protected function validate($user, $id = null)
    {
        Validator::required($user, ['first_name', 'last_name', 'email']);
        Validator::length($user['first_name'], 'first_name', 2, 60);
        Validator::length($user['last_name'], 'last_name', 2, 60);
        Validator::email($user['email']);
        if (!empty($user['phone'])) {
            if (!preg_match('/^[0-9+\-() ]{6,30}$/', $user['phone'])) {
                throw new ValidationException("Field 'phone' may contain only digits, spaces and + - ( ), 6-30 characters.");
            }
        }
        Validator::one_of($user['role'] ?? null, 'role', self::ROLES);

        // Rule: e-mail addresses are unique
        $existing = $this->dao->get_by_email($user['email']);
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("The e-mail '{$user['email']}' is already registered.");
        }
    }
}
