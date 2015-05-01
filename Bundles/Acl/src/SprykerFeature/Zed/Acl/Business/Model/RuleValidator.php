<?php

namespace SprykerFeature\Zed\Acl\Business\Model;

use SprykerFeature\Shared\Acl\Transfer\Rule;
use SprykerFeature\Shared\Acl\Transfer\RuleCollection;
use SprykerFeature\Zed\Acl\AclConfig;
use SprykerFeature\Zed\Acl\Business\AclSettings;

class RuleValidator implements RuleValidatorInterface
{
    /**
     * @var array
     */
    protected $allowedRules = [];

    /**
     * @var array
     */
    protected $deniedRules = [];

    /**
     * @param array $rules
     * @param bool $condition
     *
     * @return bool
     */
    protected function validate(array $rules, $condition)
    {
        $count = 0;
        $total = count($rules);
        foreach ($rules as $value) {
            if ($value === $condition) {
                $count++;
            }
        }

        return $count === $total;
    }

    /**
     * @param array $rules
     * @param bool $condition
     *
     * @return array
     */
    protected function reset(array $rules, $condition)
    {
        $result = [];

        foreach ($rules as $key => $value) {
            $result[$key] = $condition;
        }

        return $result;
    }

    /**
     * @param RuleCollection $rules
     *
     * @return RuleValidator $this
     */
    public function setRules(RuleCollection $rules)
    {
        foreach ($rules as $rule) {
            if ($rule->getType() === "allow") {
                $this->addAllowedRule($rule);
            }
        }

        foreach ($rules as $rule) {
            if ($rule->getType() === "deny") {
                $this->addDeniedRule($rule);
            }
        }

        return $this;
    }

    /**
     * @param Rule $rule
     */
    public function addRule(Rule $rule)
    {
        switch ($rule->getType()) {
            case 'allow':
                $this->addAllowedRule($rule);
                break;

            case 'deny':
                $this->addDeniedRule($rule);
                break;
        }
    }

    /**
     * @param Rule $rule
     *
     * @return int
     */
    protected function addAllowedRule(Rule $rule)
    {
        return array_push($this->allowedRules, $rule);
    }

    /**
     * @param Rule $rule
     *
     * @return int
     */
    protected function addDeniedRule(Rule $rule)
    {
        return array_push($this->deniedRules, $rule);
    }

    /**
     * @return array
     */
    public function getAllowedRules()
    {
        return $this->allowedRules;
    }

    /**
     * @return array
     */
    public function getDeniedRules()
    {
        return $this->deniedRules;
    }

    /**
     * @param Rule $rule
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public function assert(Rule $rule, $bundle, $controller, $action)
    {
        if (($rule->getBundle() === $bundle || $rule->getBundle() === AclConfig::VALIDATOR_WILDCARD) &&
            ($rule->getController() === $controller || $rule->getController() === AclConfig::VALIDATOR_WILDCARD) &&
            ($rule->getAction() === $action || $rule->getAction() === AclConfig::VALIDATOR_WILDCARD)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public function isAccessible($bundle, $controller, $action)
    {
        foreach ($this->getDeniedRules() as $rule) {
            if ($this->assert($rule, $bundle, $controller, $action)) {
                return false;
            }
        }

        foreach ($this->getAllowedRules() as $rule) {
            if ($this->assert($rule, $bundle, $controller, $action)) {
                return true;
            }
        }

        return false;
    }
}