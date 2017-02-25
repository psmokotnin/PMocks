<?php
namespace PMocks\Rewriter;


/**
 * Rule interface describe what methods you must describe for a new rule.
 */
interface RuleInterface
{
    
    /**
     * Apply current rule to tokens. This method can modify tokens and return it.
     * 
     * @access public
     * @param array $tokens
     * @return modified tokens array
     */
    public function apply($tokens);
    
    
    /**
     * Return class name for this rule.
     * 
     * @access public
     * @return string Class name
     */
    public function getClass();
    
    
    /**
     * Set class name for this rule.
     * 
     * @access public
     * @param string $clasName
     * @return void
     */
    public function setClass($clasName);
}