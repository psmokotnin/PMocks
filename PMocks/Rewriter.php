<?php
namespace PMocks;
/**
 * Rewriter class get original code of file, apply the given rules to it and return modefied code.
 */
class Rewriter
{
    protected
        $code   = '',
        $tokens = array(),
        $rules  = array();
    
    /**
     * __construct function.
     * 
     * @access public
     * @param string $code Original code.
     */
    public function __construct($code)
    {
        $this->code = $code;
        $this->readTokens();
    }
    
    
    /**
     * Read tokens from code.
     * 
     * @access public
     * @return void
     */
    public function readTokens()
    {
        $tokens = token_get_all($this->code);
        $line = 1;
        $this->tokens = array();
        foreach ($tokens as $token) {
            if (is_array($token))
                $line = $token[2];

            $this->tokens[] = new Rewriter\Token($token, $line);
        }
    }
    
    /**
     * Apply the rule for the code.
     * 
     * @access public
     * @param Rewriter\Rule $rule
     * @return void
     */
    public function addRule(Rewriter\RuleInterface $rule)
    {
        $this->rules[] = $rule;
    }
    
    /**
     * Return modefied code.
     * 
     * @access public
     * @return string
     */
    public function getCode()
    {
        $tokens = $this->tokens;
        foreach ($this->rules as $rule) {
            $tokens = $rule->apply($tokens);
        }
        return implode('', $tokens);
    }
    
}