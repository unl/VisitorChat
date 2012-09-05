<?php
class SpamDetector
{
    protected $rules = array();

    /**
     * Checks the variable for spam.
     *
     * @pram $potentialSpam string
     * @pram $type string
     */
    function isSpam($potentialSpam, $type = 'text')
    {
        //Do we have rules set for this type?
        if (!isset($this->rules[$type]) || !is_array($this->rules[$type])) {
            return false;
        }

        //Check if this is spam
        foreach ($this->rules[$type] as $rule) {
            if ($rule($potentialSpam)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a rule of a given type.
     *
     * @throws Exception
     *
     * @param $rule Function
     * @param $type String
     *
     * @return self
     */
    function addRule($rule, $type = 'text')
    {
        $type = strtolower($type);

        if (!is_callable($rule)) {
            throw new Exception("The rule must be a function");
        }

        $this->rules[$type][] = $rule;
        return $this;
    }

    /**
     * Sets the rules for the spam detector.
     *
     * @param array $rules
     */
    function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Get the current list of rules for the spam detector.
     *
     * @return array
     */
    function getRules()
    {
        return $this->rules;
    }
}