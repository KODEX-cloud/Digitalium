<?php
namespace App\Helpers;

class Validator {
    private array $errors = [];

    /**
     * Validate data against specified rules.
     */
    public function validate(array $data, array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $ruleset) {
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $ruleset);

            foreach ($ruleArray as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule);
                    $params = explode(',', $paramStr);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $isValid = call_user_func_array([$this, $method], [$field, $value, $params, $data]);
                    if (!$isValid) {
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get all validation errors.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Add a manual error.
     */
    public function addError(string $field, string $message): void {
        $this->errors[$field] = $message;
    }

    // Rules implementations

    private function validateRequired(string $field, mixed $value): bool {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->addError($field, "Le champ " . str_replace('_', ' ', $field) . " est requis.");
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, mixed $value): bool {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "L'adresse email est invalide.");
            return false;
        }
        return true;
    }

    private function validateMin(string $field, mixed $value, array $params): bool {
        $min = (int)($params[0] ?? 0);
        if (!empty($value) && strlen($value) < $min) {
            $this->addError($field, "Le champ doit contenir au moins {$min} caractères.");
            return false;
        }
        return true;
    }

    private function validateMax(string $field, mixed $value, array $params): bool {
        $max = (int)($params[0] ?? 0);
        if (!empty($value) && strlen($value) > $max) {
            $this->addError($field, "Le champ ne peut pas dépasser {$max} caractères.");
            return false;
        }
        return true;
    }

    private function validateMatch(string $field, mixed $value, array $params, array $data): bool {
        $matchField = $params[0] ?? '';
        $matchValue = $data[$matchField] ?? null;
        if ($value !== $matchValue) {
            $this->addError($field, "Les deux champs ne correspondent pas.");
            return false;
        }
        return true;
    }

    private function validateAlphaNumeric(string $field, mixed $value): bool {
        if (!empty($value) && !ctype_alnum(str_replace(['_', '-'], '', $value))) {
            $this->addError($field, "Le champ ne peut contenir que des lettres, des chiffres, des tirets et des underscores.");
            return false;
        }
        return true;
    }
}
