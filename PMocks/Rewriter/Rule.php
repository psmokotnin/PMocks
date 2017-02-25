<?php
namespace PMocks\Rewriter;


/**
 * Rule interface describe what methods you must describe for a new rule.
 */
interface Rule
{
    public function apply($tokens);
    public function getClass();
    public function setClass($clasName);
}