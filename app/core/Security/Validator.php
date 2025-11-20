<?php
/**
 * Form Validation Class
 */

namespace App\Core\Security;

class Validator
{
    private $errors = [];

    /**
     * Validate data
     * 
     * @param array $data
     * @param array $rules
     * @return bool
     */
    public function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;

                if (!$this->validateRule($field, $value, $ruleName, $ruleValue, $data)) {
                    break; // Stop validation for this field on first error
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate single rule
     * 
     * @param string $field
     * @param mixed $value
     * @param string $ruleName
     * @param mixed $ruleValue
     * @param array $data
     * @return bool
     */
    private function validateRule($field, $value, $ruleName, $ruleValue, $data)
    {
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field] = ucfirst($field) . ' is required';
                    return false;
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a valid email address';
                    return false;
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    $this->errors[$field] = ucfirst($field) . ' must be at least ' . $ruleValue . ' characters';
                    return false;
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    $this->errors[$field] = ucfirst($field) . ' must not exceed ' . $ruleValue . ' characters';
                    return false;
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a number';
                    return false;
                }
                break;

            case 'match':
                if (!empty($value) && $value !== ($data[$ruleValue] ?? null)) {
                    $this->errors[$field] = ucfirst($field) . ' does not match';
                    return false;
                }
                break;

            case 'unique':
                // This would require database check - implement in specific validators
                break;
        }

        return true;
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get first error
     * 
     * @return string|null
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Sanitize input
     * 
     * @param mixed $data
     * @return mixed
     */
    public function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

