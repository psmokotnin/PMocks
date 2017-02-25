<?php
namespace PMocks\Rewriter;


/**
 * Abstract RuleAbstract class.
 * Allow you to create your own mock rule.
 * 
 * @abstract
 */
abstract class RuleAbstract
{
    protected
        $className = false;

    public function getClass()
    {
        return $this->className;
    }
    
    public function setClass($clasName)
    {
        $this->className = $clasName;
        return $this;
    }
}