<?php
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';

/**
 * BaseService: common business logic for every entity.
 *
 * Problems are signalled with EXCEPTIONS (utils/exceptions.php) that carry an HTTP status code.
 * Child services override validate() with their own rules (and normalize() when input must be
 * cleaned first) and can override any CRUD method when an entity needs special behaviour.
 * Services are the ONLY layer that calls the DAOs.
 */
class BaseService
{
    protected $dao;

    /** Used in error messages, e.g. "Trainer not found." */
    protected $entity_name = 'Entity';

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function get_all()
    {
        return $this->dao->get_all();
    }

    public function get_by_id($id)
    {
        $entity = $this->dao->get_by_id($id);
        if (!$entity) {
            throw new NotFoundException("{$this->entity_name} not found.");
        }
        return $entity;
    }

    public function add($entity)
    {
        $entity = $this->normalize($entity);
        $this->validate($entity);
        $created = $this->dao->add($entity);
        return $this->get_by_id($created['id']);
    }

    /**
     * Updates an entity. The new values are merged into the saved row first, so the client may send
     * only the fields it wants to change (PUT and PATCH behave the same) and validate() always
     * sees the complete entity.
     */
    public function update($entity, $id)
    {
        $existing = $this->get_by_id($id);                 // 404 if it does not exist
        $merged = array_merge($existing, $this->normalize($entity));
        $this->validate($merged, $id);
        $this->dao->update($merged, $id);
        return $this->get_by_id($id);
    }

    public function delete($id)
    {
        $this->get_by_id($id);
        $this->dao->delete($id);
    }

    /** Cleans the input before validation. By default: trims every string. */
    protected function normalize($entity)
    {
        if (!is_array($entity)) {
            throw new ValidationException('The request body must be a JSON object.');
        }
        foreach ($entity as $key => $value) {
            if (is_string($value)) {
                $entity[$key] = trim($value);
            }
        }
        return $entity;
    }

    /**
     * Throw a ValidationException when $entity breaks a rule.
     * $id is null when creating and the entity id when updating.
     */
    protected function validate($entity, $id = null)
    {
    }

    /** Turns empty strings into NULL for optional columns (an empty description is not a description). */
    protected function empty_to_null($entity, $fields)
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $entity) && trim((string) $entity[$field]) === '') {
                $entity[$field] = null;
            }
        }
        return $entity;
    }
}
